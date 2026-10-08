<?php

namespace Database\Seeders;

use App\Models\Developer;
use App\Models\Game;
use App\Models\GameRequirement;
use App\Models\Genre;
use App\Models\Platform;
use App\Models\Publisher;
use App\Models\Requirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Throwable;

class GameImportSeeder extends Seeder
{
    private int $added = 0;
    private int $updated = 0;
    private int $unchanged = 0;
    private int $failed = 0;

    public function run(): void
    {
        $path = database_path('data/newGame.json');

        if (!File::exists($path)) {
            $this->command->error(
                'newGame.json not found.'
            );

            return;
        }

        try {
            $games = json_decode(
                File::get($path),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $exception) {
            $this->command->error(
                'Invalid JSON: ' . $exception->getMessage()
            );

            Log::error(
                'GameImportSeeder JSON decode failed.',
                [
                    'exception' => $exception,
                ]
            );

            return;
        }

        if (!is_array($games) || !array_is_list($games)) {
            $this->command->error(
                'Invalid JSON format. Root must be a list of games.'
            );

            return;
        }

        if (count($games) === 0) {
            $this->command->error(
                'The JSON file contains no games.'
            );

            return;
        }

        /*
         * Validate everything before touching the database.
         */
        try {
            $this->validateGames($games);
        } catch (Throwable $exception) {
            $this->command->error(
                'Validation failed: ' .
                $exception->getMessage()
            );

            Log::error(
                'GameImportSeeder validation failed.',
                [
                    'exception' => $exception,
                ]
            );

            return;
        }

        /*
         * Every Game has its own transaction.
         *
         * A failed Game is rolled back without affecting
         * previously successful Games.
         */
        foreach ($games as $gameData) {
            try {
                $status = DB::transaction(
                    function () use ($gameData) {
                        return $this->importGame($gameData);
                    }
                );

                /*
                 * Counters are changed only after a successful
                 * transaction commit.
                 */
                match ($status) {
                    'added' => $this->added++,
                    'updated' => $this->updated++,
                    'unchanged' => $this->unchanged++,
                    default => null,
                };

                $this->command->info(
                    match ($status) {
                        'added' =>
                            "[added] {$gameData['title']}",

                        'updated' =>
                            "[updated] {$gameData['title']}",

                        default =>
                            "[unchanged] {$gameData['title']}",
                    }
                );
            } catch (Throwable $exception) {
                $this->failed++;

                $title =
                    $gameData['title'] ?? 'Unknown Game';

                $this->command->error(
                    "[failed] {$title}: {$exception->getMessage()}"
                );

                Log::error(
                    'GameImportSeeder failed.',
                    [
                        'game_title' => $title,
                        'exception' => $exception,
                    ]
                );
            }
        }

        $this->command->newLine();

        $this->command->info(
            'Import finished.'
        );

        $this->command->info(
            "Added: {$this->added}"
        );

        $this->command->info(
            "Updated: {$this->updated}"
        );

        $this->command->info(
            "Unchanged: {$this->unchanged}"
        );

        $this->command->info(
            "Failed: {$this->failed}"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Game Import
    |--------------------------------------------------------------------------
    */

    private function importGame(array $gameData): string
    {
        $changed = false;

        /*
         * Developer
         */
        $developerResult =
            $this->syncDeveloper(
                $gameData['developer']
            );

        $developer =
            $developerResult['model'];

        if ($developerResult['changed']) {
            $changed = true;
        }

        /*
         * Publisher
         */
        $publisherResult =
            $this->syncPublisher(
                $gameData['publisher']
            );

        $publisher =
            $publisherResult['model'];

        if ($publisherResult['changed']) {
            $changed = true;
        }

        /*
         * Game
         */
        $title = trim(
            $gameData['title']
        );

        $game = Game::query()
            ->whereRaw(
                'LOWER(TRIM(title)) = ?',
                [$this->normalize($title)]
            )
            ->first();

        $isNewGame = !$game;

        if (!$game) {
            $game = new Game();

            $game->title =
                $title;

            $game->description =
                $gameData['description'];

            $game->release_date =
                $gameData['release_date'] ?? null;

            $game->developer_id =
                $developer->id;

            $game->publisher_id =
                $publisher->id;

            $game->website =
                $gameData['website'] ?? null;

            $game->status =
                $gameData['status'] ?? null;

            $game->save();

            $changed = true;
        } else {
            /*
             * JSON is authoritative.
             */
            $game->fill([
                'title' =>
                    $title,

                'description' =>
                    $gameData['description'],

                'release_date' =>
                    $gameData['release_date'] ?? null,

                'developer_id' =>
                    $developer->id,

                'publisher_id' =>
                    $publisher->id,

                'website' =>
                    $gameData['website'] ?? null,

                'status' =>
                    $gameData['status'] ?? null,
            ]);

            if ($game->isDirty()) {
                $game->save();

                $changed = true;
            }
        }

        /*
         * Genres
         */
        if (
            $this->syncGenres(
                $game,
                $gameData['genres']
            )
        ) {
            $changed = true;
        }

        /*
         * Platforms + Requirements
         */
        if (
            $this->syncPlatforms(
                $game,
                $gameData['platforms']
            )
        ) {
            $changed = true;
        }

        /*
         * Images
         */
        if (
            $this->syncImages(
                $game,
                $gameData['images']
            )
        ) {
            $changed = true;
        }

        /*
         * Ratings
         */
        if (
            $this->syncRatings(
                $game,
                $gameData['ratings']
            )
        ) {
            $changed = true;
        }

        if ($isNewGame) {
            return 'added';
        }

        return $changed
            ? 'updated'
            : 'unchanged';
    }

    /*
    |--------------------------------------------------------------------------
    | Developer
    |--------------------------------------------------------------------------
    */

    private function syncDeveloper(array $data): array
    {
        $name = trim(
            $data['name']
        );

        $developer = Developer::query()
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [$this->normalize($name)]
            )
            ->first();

        if (!$developer) {
            return [
                'model' =>
                    Developer::create([
                        'name' =>
                            $name,

                        'logo' =>
                            $data['logo'] ?? null,

                        'website' =>
                            $data['website'] ?? null,
                    ]),

                'changed' =>
                    true,
            ];
        }

        $developer->fill([
            'name' =>
                $name,

            'logo' =>
                $data['logo'] ?? null,

            'website' =>
                $data['website'] ?? null,
        ]);

        $changed =
            $developer->isDirty();

        if ($changed) {
            $developer->save();
        }

        return [
            'model' =>
                $developer,

            'changed' =>
                $changed,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Publisher
    |--------------------------------------------------------------------------
    */

    private function syncPublisher(array $data): array
    {
        $name = trim(
            $data['name']
        );

        $publisher = Publisher::query()
            ->whereRaw(
                'LOWER(TRIM(name)) = ?',
                [$this->normalize($name)]
            )
            ->first();

        if (!$publisher) {
            return [
                'model' =>
                    Publisher::create([
                        'name' =>
                            $name,

                        'logo' =>
                            $data['logo'] ?? null,

                        'website' =>
                            $data['website'] ?? null,
                    ]),

                'changed' =>
                    true,
            ];
        }

        $publisher->fill([
            'name' =>
                $name,

            'logo' =>
                $data['logo'] ?? null,

            'website' =>
                $data['website'] ?? null,
        ]);

        $changed =
            $publisher->isDirty();

        if ($changed) {
            $publisher->save();
        }

        return [
            'model' =>
                $publisher,

            'changed' =>
                $changed,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Genres
    |--------------------------------------------------------------------------
    */

    private function syncGenres(
        Game $game,
        array $genres
    ): bool {
        $genreIds = [];

        foreach ($genres as $genreName) {
            $name = trim(
                $genreName
            );

            $genre = Genre::query()
                ->whereRaw(
                    'LOWER(TRIM(name)) = ?',
                    [$this->normalize($name)]
                )
                ->first();

            if (!$genre) {
                $genre = Genre::create([
                    'name' =>
                        $name,
                ]);
            } else {
                $genre->fill([
                    'name' =>
                        $name,
                ]);

                if ($genre->isDirty()) {
                    $genre->save();
                }
            }

            $genreIds[] =
                $genre->id;
        }

        $existingIds = $game->genres()
            ->pluck('genres.id')
            ->sort()
            ->values()
            ->all();

        $newIds = collect($genreIds)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $changed =
            $existingIds !== $newIds;

        $game->genres()->sync(
            $newIds
        );

        return $changed;
    }

    /*
    |--------------------------------------------------------------------------
    | Platforms
    |--------------------------------------------------------------------------
    */

    private function syncPlatforms(
        Game $game,
        array $platforms
    ): bool {
        $changed = false;

        $existingPivots = $game->platforms()
            ->withPivot([
                'version',
                'release_date',
                'download_size',
                'game_requirement_id',
            ])
            ->get()
            ->keyBy('id');

        $pivotData = [];

        foreach ($platforms as $platformData) {
            $platformName =
                trim(
                    $platformData['name']
                );

            $platform = Platform::query()
                ->whereRaw(
                    'LOWER(TRIM(name)) = ?',
                    [$this->normalize($platformName)]
                )
                ->first();

            if (!$platform) {
                $platform = Platform::create([
                    'name' =>
                        $platformName,

                    'logo' =>
                        $platformData['logo'] ?? null,
                ]);

                $changed = true;
            } else {
                $platform->fill([
                    'name' =>
                        $platformName,

                    'logo' =>
                        $platformData['logo'] ?? null,
                ]);

                if ($platform->isDirty()) {
                    $platform->save();

                    $changed = true;
                }
            }

            $existingPivot =
                $existingPivots->get(
                    $platform->id
                );

            $oldGameRequirementId =
                $existingPivot?->pivot
                    ?->game_requirement_id;

            /*
             * Prepare the new GameRequirement.
             */
            $requirementResult =
                $this->prepareGameRequirement(
                    $platformData['requirement'] ?? null,
                    $oldGameRequirementId
                );

            $newGameRequirementId =
                $requirementResult['id'];

            if ($requirementResult['changed']) {
                $changed = true;
            }

            /*
             * The old GameRequirement is no longer used
             * by this pivot.
             *
             * The FK must be nullable for this operation.
             */
            if (
                $existingPivot &&
                $oldGameRequirementId !== null &&
                $oldGameRequirementId !==
                $newGameRequirementId
            ) {
                DB::table('game_platform')
                    ->where(
                        'game_id',
                        $game->id
                    )
                    ->where(
                        'platform_id',
                        $platform->id
                    )
                    ->where(
                        'game_requirement_id',
                        $oldGameRequirementId
                    )
                    ->update([
                        'game_requirement_id' =>
                            null,
                    ]);

                $this->cleanupGameRequirement(
                    $oldGameRequirementId
                );
            }

            $newPivot = [
                'version' =>
                    $platformData['version'] ?? null,

                'release_date' =>
                    $platformData['release_date'] ?? null,

                'download_size' =>
                    $platformData['download_size'] ?? null,

                'game_requirement_id' =>
                    $newGameRequirementId,
            ];

            if ($existingPivot) {
                $oldPivot = [
                    'version' =>
                        $existingPivot
                            ->pivot
                            ->version,

                    'release_date' =>
                        $existingPivot
                            ->pivot
                            ->release_date,

                    'download_size' =>
                        $existingPivot
                            ->pivot
                            ->download_size,

                    'game_requirement_id' =>
                        $existingPivot
                            ->pivot
                            ->game_requirement_id,
                ];

                if ($oldPivot !== $newPivot) {
                    $changed = true;
                }
            } else {
                $changed = true;
            }

            $pivotData[
                $platform->id
            ] = $newPivot;
        }

        /*
         * Platforms removed from JSON.
         */
        $newPlatformIds =
            array_map(
                'intval',
                array_keys($pivotData)
            );

        foreach (
            $existingPivots
            as $platformId => $existingPivot
        ) {
            if (
                in_array(
                    (int) $platformId,
                    $newPlatformIds,
                    true
                )
            ) {
                continue;
            }

            $oldGameRequirementId =
                $existingPivot
                    ->pivot
                    ->game_requirement_id;

            if ($oldGameRequirementId !== null) {
                DB::table('game_platform')
                    ->where(
                        'game_id',
                        $game->id
                    )
                    ->where(
                        'platform_id',
                        $platformId
                    )
                    ->where(
                        'game_requirement_id',
                        $oldGameRequirementId
                    )
                    ->update([
                        'game_requirement_id' =>
                            null,
                    ]);

                $this->cleanupGameRequirement(
                    $oldGameRequirementId
                );
            }

            $changed = true;
        }

        /*
         * Final pivot state.
         */
        $game->platforms()->sync(
            $pivotData
        );

        return $changed;
    }

    /*
    |--------------------------------------------------------------------------
    | Game Requirement
    |--------------------------------------------------------------------------
    */

    private function prepareGameRequirement(
        ?array $requirementData,
        ?int $existingGameRequirementId
    ): array {
        /*
         * No requirement.
         */
        if (
            $requirementData === null ||
            $requirementData === []
        ) {
            return [
                'id' =>
                    null,

                'changed' =>
                    $existingGameRequirementId !== null,
            ];
        }

        $minimumData =
            $this->normalizeRequirementData(
                $requirementData['minimum'] ?? null
            );

        $recommendedData =
            $this->normalizeRequirementData(
                $requirementData['recommended'] ?? null
            );

        /*
         * No existing GameRequirement.
         */
        if ($existingGameRequirementId === null) {
            return $this->createGameRequirement(
                $minimumData,
                $recommendedData
            );
        }

        $existingGameRequirement =
            GameRequirement::find(
                $existingGameRequirementId
            );

        /*
         * Referenced GameRequirement no longer exists.
         */
        if (!$existingGameRequirement) {
            return $this->createGameRequirement(
                $minimumData,
                $recommendedData
            );
        }

        $oldMinimumId =
            $existingGameRequirement
                ->minimum_requirement_id;

        $oldRecommendedId =
            $existingGameRequirement
                ->recommended_requirement_id;

        /*
         * IMPORTANT:
         *
         * A GameRequirement itself can be shared by multiple
         * game_platform rows.
         *
         * If it is shared and the requested snapshot differs,
         * NEVER mutate the shared GameRequirement.
         *
         * Instead create a new GameRequirement for the current
         * platform. syncPlatforms() will move the current pivot
         * to the new row and cleanup the old one only if unused.
         */
        $gameRequirementUsageCount =
            DB::table('game_platform')
                ->where(
                    'game_requirement_id',
                    $existingGameRequirementId
                )
                ->count();

        if ($gameRequirementUsageCount > 1) {
            $currentMinimumData =
                $this->getRequirementData(
                    $oldMinimumId
                );

            $currentRecommendedData =
                $this->getRequirementData(
                    $oldRecommendedId
                );

            if (
                $currentMinimumData ===
                    $minimumData &&
                $currentRecommendedData ===
                    $recommendedData
            ) {
                /*
                 * Exact same snapshot.
                 * Reuse the shared GameRequirement.
                 */
                return [
                    'id' =>
                        $existingGameRequirement->id,

                    'changed' =>
                        false,
                ];
            }

            /*
             * Snapshot changed.
             *
             * Create an independent GameRequirement.
             */
            return $this->createGameRequirement(
                $minimumData,
                $recommendedData
            );
        }

        /*
         * Special case:
         *
         * Minimum and recommended currently point to the
         * same Requirement, but the new JSON requires two
         * different snapshots.
         */
        if (
            $oldMinimumId &&
            $oldMinimumId === $oldRecommendedId &&
            $minimumData !== null &&
            $recommendedData !== null &&
            $minimumData !== $recommendedData
        ) {
            $minimum =
                Requirement::create(
                    $minimumData
                );

            $recommended =
                Requirement::create(
                    $recommendedData
                );

            $existingGameRequirement->fill([
                'minimum_requirement_id' =>
                    $minimum->id,

                'recommended_requirement_id' =>
                    $recommended->id,
            ]);

            $existingGameRequirement->save();

            /*
             * The old shared Requirement is now potentially
             * unused.
             */
            $this->deleteRequirementIfUnused(
                $oldMinimumId
            );

            return [
                'id' =>
                    $existingGameRequirement->id,

                'changed' =>
                    true,
            ];
        }

        /*
         * Resolve minimum.
         */
        $minimumResult =
            $this->resolveRequirement(
                $oldMinimumId,
                $minimumData
            );

        $minimum =
            $minimumResult['model'];

        /*
         * If minimum and recommended have exactly the same
         * data, they can safely share one Requirement.
         */
        if (
            $minimumData !== null &&
            $recommendedData !== null &&
            $minimumData === $recommendedData
        ) {
            if ($minimum) {
                $recommended =
                    $minimum;

                $recommendedChanged =
                    false;
            } else {
                $recommendedResult =
                    $this->resolveRequirement(
                        $oldRecommendedId,
                        $recommendedData
                    );

                $recommended =
                    $recommendedResult['model'];

                $recommendedChanged =
                    $recommendedResult['changed'];
            }
        } else {
            /*
             * Resolve recommended normally.
             */
            $recommendedResult =
                $this->resolveRequirement(
                    $oldRecommendedId,
                    $recommendedData
                );

            $recommended =
                $recommendedResult['model'];

            $recommendedChanged =
                $recommendedResult['changed'];
        }

        $changed =
            $minimumResult['changed'] ||
            $recommendedChanged;

        /*
         * Save new references.
         */
        $existingGameRequirement->fill([
            'minimum_requirement_id' =>
                $minimum?->id,

            'recommended_requirement_id' =>
                $recommended?->id,
        ]);

        if ($existingGameRequirement->isDirty()) {
            $existingGameRequirement->save();

            $changed = true;
        }

        /*
         * Remove old Requirement rows that are no longer
         * referenced anywhere.
         */
        $newRequirementIds =
            array_values(
                array_unique(
                    array_filter([
                        $minimum?->id,
                        $recommended?->id,
                    ])
                )
            );

        $oldRequirementIds =
            array_values(
                array_unique(
                    array_filter([
                        $oldMinimumId,
                        $oldRecommendedId,
                    ])
                )
            );

        foreach (
            $oldRequirementIds
            as $oldRequirementId
        ) {
            if (
                in_array(
                    $oldRequirementId,
                    $newRequirementIds,
                    true
                )
            ) {
                continue;
            }

            $this->deleteRequirementIfUnused(
                (int) $oldRequirementId
            );
        }

        /*
         * Both references disappeared.
         *
         * Do not delete GameRequirement here because the
         * game_platform pivot may still reference it.
         *
         * syncPlatforms() handles the pivot and cleanup.
         */
        if (
            $minimum === null &&
            $recommended === null
        ) {
            return [
                'id' =>
                    null,

                'changed' =>
                    true,
            ];
        }

        return [
            'id' =>
                $existingGameRequirement->id,

            'changed' =>
                $changed,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Create Game Requirement
    |--------------------------------------------------------------------------
    */

    private function createGameRequirement(
        ?array $minimumData,
        ?array $recommendedData
    ): array {
        /*
         * No requirement at all.
         */
        if (
            $minimumData === null &&
            $recommendedData === null
        ) {
            return [
                'id' =>
                    null,

                'changed' =>
                    false,
            ];
        }

        /*
         * Same snapshot can safely use one Requirement
         * for both minimum and recommended.
         */
        if (
            $minimumData !== null &&
            $recommendedData !== null &&
            $minimumData === $recommendedData
        ) {
            $minimum =
                Requirement::create(
                    $minimumData
                );

            $gameRequirement =
                GameRequirement::create([
                    'minimum_requirement_id' =>
                        $minimum->id,

                    'recommended_requirement_id' =>
                        $minimum->id,
                ]);

            return [
                'id' =>
                    $gameRequirement->id,

                'changed' =>
                    true,
            ];
        }

        /*
         * Minimum.
         */
        $minimum = null;

        if ($minimumData !== null) {
            $minimum =
                Requirement::create(
                    $minimumData
                );
        }

        /*
         * Recommended.
         */
        $recommended = null;

        if ($recommendedData !== null) {
            $recommended =
                Requirement::create(
                    $recommendedData
                );
        }

        $gameRequirement =
            GameRequirement::create([
                'minimum_requirement_id' =>
                    $minimum?->id,

                'recommended_requirement_id' =>
                    $recommended?->id,
            ]);

        return [
            'id' =>
                $gameRequirement->id,

            'changed' =>
                true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Requirement
    |--------------------------------------------------------------------------
    */

    private function resolveRequirement(
        ?int $oldRequirementId,
        ?array $newData
    ): array {
        /*
         * Requirement was removed.
         */
        if ($newData === null) {
            return [
                'model' =>
                    null,

                'changed' =>
                    $oldRequirementId !== null,
            ];
        }

        /*
         * Existing Requirement.
         */
        if ($oldRequirementId !== null) {
            $requirement =
                Requirement::find(
                    $oldRequirementId
                );

            if ($requirement) {
                /*
                 * Count distinct GameRequirement rows,
                 * not references.
                 *
                 * If one GameRequirement uses the same
                 * Requirement for both minimum and recommended,
                 * it counts as ONE owner.
                 */
                $usageCount =
                    GameRequirement::query()
                        ->where(function ($query) use ($requirement) {
                            $query
                                ->where(
                                    'minimum_requirement_id',
                                    $requirement->id
                                )
                                ->orWhere(
                                    'recommended_requirement_id',
                                    $requirement->id
                                );
                        })
                        ->distinct('id')
                        ->count('id');

                if ($usageCount <= 1) {
                    $requirement->fill(
                        $newData
                    );

                    if ($requirement->isDirty()) {
                        $requirement->save();

                        return [
                            'model' =>
                                $requirement,

                            'changed' =>
                                true,
                        ];
                    }

                    return [
                        'model' =>
                            $requirement,

                        'changed' =>
                            false,
                    ];
                }

                /*
                 * Shared Requirement.
                 *
                 * Never mutate it in place.
                 */
                return [
                    'model' =>
                        Requirement::create(
                            $newData
                        ),

                    'changed' =>
                        true,
                ];
            }
        }

        /*
         * No previous Requirement exists.
         */
        return [
            'model' =>
                Requirement::create(
                    $newData
                ),

            'changed' =>
                true,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Requirement Data Snapshot
    |--------------------------------------------------------------------------
    */

    private function getRequirementData(
        ?int $requirementId
    ): ?array {
        if ($requirementId === null) {
            return null;
        }

        $requirement =
            Requirement::find(
                $requirementId
            );

        if (!$requirement) {
            return null;
        }

        return [
            'ram' =>
                $requirement->ram,

            'system_version' =>
                $requirement->system_version,

            'cpu' =>
                $requirement->cpu,

            'gpu' =>
                $requirement->gpu,

            'storage' =>
                $requirement->storage,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Requirement Cleanup
    |--------------------------------------------------------------------------
    */

    private function cleanupGameRequirement(
        int $gameRequirementId
    ): void {
        /*
         * Do not delete a GameRequirement that is still
         * referenced by another game_platform row.
         */
        $isStillUsed =
            DB::table('game_platform')
                ->where(
                    'game_requirement_id',
                    $gameRequirementId
                )
                ->exists();

        if ($isStillUsed) {
            return;
        }

        $gameRequirement =
            GameRequirement::find(
                $gameRequirementId
            );

        if (!$gameRequirement) {
            return;
        }

        $requirementIds =
            array_values(
                array_unique(
                    array_filter([
                        $gameRequirement
                            ->minimum_requirement_id,

                        $gameRequirement
                            ->recommended_requirement_id,
                    ])
                )
            );

        /*
         * Delete GameRequirement first.
         */
        $gameRequirement->delete();

        /*
         * Then clean Requirement rows.
         */
        foreach (
            $requirementIds
            as $requirementId
        ) {
            $this->deleteRequirementIfUnused(
                (int) $requirementId
            );
        }
    }

    private function deleteRequirementIfUnused(
        int $requirementId
    ): void {
        $usageCount =
            GameRequirement::query()
                ->where(
                    'minimum_requirement_id',
                    $requirementId
                )
                ->orWhere(
                    'recommended_requirement_id',
                    $requirementId
                )
                ->count();

        if ($usageCount === 0) {
            Requirement::where(
                'id',
                $requirementId
            )->delete();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Images
    |--------------------------------------------------------------------------
    */

    private function syncImages(
        Game $game,
        array $images
    ): bool {
        $changed = false;

        $existingImages =
            $game->images()
                ->orderBy('id')
                ->get();

        /*
         * Icon
         */
        $iconImages =
            $existingImages
                ->where('type', 'icon')
                ->values();

        $icon =
            $iconImages->first();

        if ($icon) {
            $icon->fill([
                'path' =>
                    $images[0],

                'type' =>
                    'icon',
            ]);

            if ($icon->isDirty()) {
                $icon->save();

                $changed = true;
            }

            /*
             * Remove duplicate icon rows.
             */
            foreach (
                $iconImages->skip(1)
                as $duplicate
            ) {
                $duplicate->delete();

                $changed = true;
            }
        } else {
            $game->images()->create([
                'path' =>
                    $images[0],

                'type' =>
                    'icon',
            ]);

            $changed = true;
        }

        /*
         * Cover
         */
        $coverImages =
            $existingImages
                ->where('type', 'cover')
                ->values();

        $cover =
            $coverImages->first();

        if ($cover) {
            $cover->fill([
                'path' =>
                    $images[1],

                'type' =>
                    'cover',
            ]);

            if ($cover->isDirty()) {
                $cover->save();

                $changed = true;
            }

            /*
             * Remove duplicate cover rows.
             */
            foreach (
                $coverImages->skip(1)
                as $duplicate
            ) {
                $duplicate->delete();

                $changed = true;
            }
        } else {
            $game->images()->create([
                'path' =>
                    $images[1],

                'type' =>
                    'cover',
            ]);

            $changed = true;
        }

        /*
         * Screenshots
         */
        $existingScreenshots =
            $existingImages
                ->where('type', 'screenshot')
                ->values();

        $newScreenshots =
            array_slice(
                $images,
                2
            );

        foreach (
            $newScreenshots
            as $index => $path
        ) {
            $existingScreenshot =
                $existingScreenshots->get(
                    $index
                );

            if ($existingScreenshot) {
                $existingScreenshot->fill([
                    'path' =>
                        $path,

                    'type' =>
                        'screenshot',
                ]);

                if ($existingScreenshot->isDirty()) {
                    $existingScreenshot->save();

                    $changed = true;
                }
            } else {
                $game->images()->create([
                    'path' =>
                        $path,

                    'type' =>
                        'screenshot',
                ]);

                $changed = true;
            }
        }

        /*
         * Remove screenshots no longer present.
         *
         * Physical files are intentionally preserved.
         */
        if (
            $existingScreenshots->count() >
            count($newScreenshots)
        ) {
            foreach (
                $existingScreenshots->slice(
                    count($newScreenshots)
                )
                as $extraScreenshot
            ) {
                $extraScreenshot->delete();

                $changed = true;
            }
        }

        return $changed;
    }

    /*
    |--------------------------------------------------------------------------
    | Ratings
    |--------------------------------------------------------------------------
    */

    private function syncRatings(
        Game $game,
        array $ratings
    ): bool {
        $changed = false;

        $existingRatings =
            $game->ratings()
                ->get()
                ->keyBy(
                    fn ($rating) =>
                        $this->normalize(
                            $rating->source
                        )
                );

        $usedRatingIds = [];

        foreach ($ratings as $ratingData) {
            $source =
                trim(
                    $ratingData['source']
                );

            $key =
                $this->normalize(
                    $source
                );

            $rating =
                $existingRatings->get(
                    $key
                );

            if (!$rating) {
                $rating =
                    $game->ratings()->create([
                        'source' =>
                            $source,

                        'logo_source' =>
                            $ratingData[
                                'logo_source'
                            ] ?? null,

                        'rating' =>
                            $ratingData['rating'],

                        'rating_count' =>
                            $ratingData[
                                'rating_count'
                            ],
                    ]);

                $changed = true;
            } else {
                $rating->fill([
                    'source' =>
                        $source,

                    'logo_source' =>
                        $ratingData[
                            'logo_source'
                        ] ?? null,

                    'rating' =>
                        $ratingData['rating'],

                    'rating_count' =>
                        $ratingData[
                            'rating_count'
                        ],
                ]);

                if ($rating->isDirty()) {
                    $rating->save();

                    $changed = true;
                }
            }

            $usedRatingIds[] =
                $rating->id;
        }

        /*
         * Remove ratings no longer present in JSON.
         */
        $query =
            $game->ratings();

        if (count($usedRatingIds) > 0) {
            $query->whereNotIn(
                'id',
                $usedRatingIds
            );
        }

        $deletedCount =
            $query->delete();

        if ($deletedCount > 0) {
            $changed = true;
        }

        return $changed;
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateGames(
        array $games
    ): void {
        $validator =
            Validator::make(
                $games,
                [
                    '*.title' => [
                        'required',
                        'string',
                        'min:1',
                    ],

                    '*.description' => [
                        'required',
                        'string',
                    ],

                    '*.release_date' => [
                        'nullable',
                        'date',
                    ],

                    '*.website' => [
                        'nullable',
                        'string',
                    ],

                    '*.status' => [
                        'nullable',
                        'string',
                    ],

                    '*.developer' => [
                        'required',
                        'array',
                    ],

                    '*.developer.name' => [
                        'required',
                        'string',
                        'min:1',
                    ],

                    '*.developer.logo' => [
                        'nullable',
                        'string',
                    ],

                    '*.developer.website' => [
                        'nullable',
                        'string',
                    ],

                    '*.publisher' => [
                        'required',
                        'array',
                    ],

                    '*.publisher.name' => [
                        'required',
                        'string',
                        'min:1',
                    ],

                    '*.publisher.logo' => [
                        'nullable',
                        'string',
                    ],

                    '*.publisher.website' => [
                        'nullable',
                        'string',
                    ],

                    '*.genres' => [
                        'required',
                        'array',
                    ],

                    '*.genres.*' => [
                        'required',
                        'string',
                        'min:1',
                    ],

                    '*.platforms' => [
                        'required',
                        'array',
                        'min:1',
                    ],

                    '*.platforms.*' => [
                        'required',
                        'array',
                    ],

                    '*.platforms.*.name' => [
                        'required',
                        'string',
                        'min:1',
                    ],

                    '*.platforms.*.logo' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.version' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.release_date' => [
                        'nullable',
                        'date',
                    ],

                    '*.platforms.*.download_size' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement' => [
                        'nullable',
                        'array',
                    ],

                    '*.platforms.*.requirement.minimum' => [
                        'nullable',
                        'array',
                        'min:1',
                    ],

                    '*.platforms.*.requirement.recommended' => [
                        'nullable',
                        'array',
                        'min:1',
                    ],

                    '*.platforms.*.requirement.minimum.ram' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.minimum.system_version' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.minimum.cpu' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.minimum.gpu' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.minimum.storage' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.recommended.ram' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.recommended.system_version' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.recommended.cpu' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.recommended.gpu' => [
                        'nullable',
                        'string',
                    ],

                    '*.platforms.*.requirement.recommended.storage' => [
                        'nullable',
                        'string',
                    ],

                    '*.images' => [
                        'required',
                        'array',
                        'min:2',
                    ],

                    '*.images.*' => [
                        'required',
                        'string',
                        'min:1',
                    ],

                    '*.ratings' => [
                        'required',
                        'array',
                    ],

                    '*.ratings.*' => [
                        'required',
                        'array',
                    ],

                    '*.ratings.*.source' => [
                        'required',
                        'string',
                        'min:1',
                    ],

                    '*.ratings.*.logo_source' => [
                        'nullable',
                        'string',
                    ],

                    '*.ratings.*.rating' => [
                        'required',
                        'numeric',
                    ],

                    '*.ratings.*.rating_count' => [
                        'required',
                        'integer',
                        'min:0',
                    ],
                ]
            );

        $validator->validate();

        /*
         * Strict JSON structure.
         */
        foreach (
            $games
            as $gameIndex => $game
        ) {
            $this->validateAllowedKeys(
                $game,
                [
                    'title',
                    'description',
                    'release_date',
                    'developer',
                    'publisher',
                    'website',
                    'status',
                    'genres',
                    'platforms',
                    'images',
                    'ratings',
                ],
                "games[{$gameIndex}]"
            );

            $this->validateAllowedKeys(
                $game['developer'],
                [
                    'name',
                    'logo',
                    'website',
                ],
                "games[{$gameIndex}].developer"
            );

            $this->validateAllowedKeys(
                $game['publisher'],
                [
                    'name',
                    'logo',
                    'website',
                ],
                "games[{$gameIndex}].publisher"
            );

            foreach (
                $game['platforms']
                as $platformIndex => $platform
            ) {
                $this->validateAllowedKeys(
                    $platform,
                    [
                        'name',
                        'logo',
                        'version',
                        'release_date',
                        'download_size',
                        'requirement',
                    ],
                    "games[{$gameIndex}].platforms[{$platformIndex}]"
                );

                if (
                    isset($platform['requirement']) &&
                    $platform['requirement'] !== null
                ) {
                    $this->validateAllowedKeys(
                        $platform['requirement'],
                        [
                            'minimum',
                            'recommended',
                        ],
                        "games[{$gameIndex}].platforms[{$platformIndex}].requirement"
                    );

                    foreach (
                        [
                            'minimum',
                            'recommended',
                        ] as $type
                    ) {
                        if (
                            isset(
                                $platform['requirement'][$type]
                            ) &&
                            $platform['requirement'][$type] !== null
                        ) {
                            $this->validateAllowedKeys(
                                $platform['requirement'][$type],
                                [
                                    'ram',
                                    'system_version',
                                    'cpu',
                                    'gpu',
                                    'storage',
                                ],
                                "games[{$gameIndex}].platforms[{$platformIndex}].requirement.{$type}"
                            );
                        }
                    }
                }
            }

            foreach (
                $game['ratings']
                as $ratingIndex => $rating
            ) {
                $this->validateAllowedKeys(
                    $rating,
                    [
                        'source',
                        'logo_source',
                        'rating',
                        'rating_count',
                    ],
                    "games[{$gameIndex}].ratings[{$ratingIndex}]"
                );
            }
        }

        /*
         * Duplicate checks.
         */
        $this->validateDuplicateData(
            $games
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Duplicate Validation
    |--------------------------------------------------------------------------
    */

    private function validateDuplicateData(
        array $games
    ): void {
        /*
         * Duplicate Game titles.
         */
        $gameTitles = [];

        foreach (
            $games
            as $index => $game
        ) {
            $key =
                $this->normalize(
                    $game['title']
                );

            if (isset($gameTitles[$key])) {
                throw new \RuntimeException(
                    "Duplicate game title " .
                    "'{$game['title']}' found at " .
                    "indexes {$gameTitles[$key]} and {$index}."
                );
            }

            $gameTitles[$key] =
                $index;
        }

        foreach ($games as $game) {
            /*
             * Duplicate Genres.
             */
            $genres = [];

            foreach (
                $game['genres']
                as $genre
            ) {
                $key =
                    $this->normalize(
                        $genre
                    );

                if (isset($genres[$key])) {
                    throw new \RuntimeException(
                        "Duplicate genre " .
                        "'{$genre}' found in game " .
                        "'{$game['title']}'."
                    );
                }

                $genres[$key] =
                    true;
            }

            /*
             * Duplicate Platforms.
             */
            $platforms = [];

            foreach (
                $game['platforms']
                as $platform
            ) {
                $key =
                    $this->normalize(
                        $platform['name']
                    );

                if (isset($platforms[$key])) {
                    throw new \RuntimeException(
                        "Duplicate platform " .
                        "'{$platform['name']}' found " .
                        "in game '{$game['title']}'."
                    );
                }

                $platforms[$key] =
                    true;
            }

            /*
             * Duplicate Ratings.
             */
            $ratings = [];

            foreach (
                $game['ratings']
                as $rating
            ) {
                $key =
                    $this->normalize(
                        $rating['source']
                    );

                if (isset($ratings[$key])) {
                    throw new \RuntimeException(
                        "Duplicate rating source " .
                        "'{$rating['source']}' found " .
                        "in game '{$game['title']}'."
                    );
                }

                $ratings[$key] =
                    true;
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Strict Keys
    |--------------------------------------------------------------------------
    */

    private function validateAllowedKeys(
        array $data,
        array $allowedKeys,
        string $path
    ): void {
        $unknownKeys =
            array_diff(
                array_keys($data),
                $allowedKeys
            );

        if (count($unknownKeys) === 0) {
            return;
        }

        throw new \RuntimeException(
            "Unknown JSON field(s) at {$path}: " .
            implode(', ', $unknownKeys)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Requirement Helpers
    |--------------------------------------------------------------------------
    */

    private function normalizeRequirementData(
        ?array $data
    ): ?array {
        if (
            $data === null ||
            $data === []
        ) {
            return null;
        }

        /*
         * JSON is authoritative.
         *
         * Missing recognized fields become null.
         */
        return [
            'ram' =>
                $data['ram'] ?? null,

            'system_version' =>
                $data['system_version'] ?? null,

            'cpu' =>
                $data['cpu'] ?? null,

            'gpu' =>
                $data['gpu'] ?? null,

            'storage' =>
                $data['storage'] ?? null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Normalization
    |--------------------------------------------------------------------------
    */

    private function normalize(
        string $value
    ): string {
        return mb_strtolower(
            trim($value),
            'UTF-8'
        );
    }
}
