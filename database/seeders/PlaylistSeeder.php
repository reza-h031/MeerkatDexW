<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Playlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('data/playlist.json');

        /*
         * Check whether the JSON file exists.
         */
        if (!File::exists($filePath)) {
            throw new RuntimeException(
                "Playlist JSON file not found: {$filePath}"
            );
        }

        /*
         * Decode JSON and report syntax errors.
         */
        try {
            $playlists = json_decode(
                File::get($filePath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (JsonException $exception) {
            throw new RuntimeException(
                'Invalid playlist.json: ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        /*
         * The root JSON value must be a list of playlists.
         */
        if (!is_array($playlists) || !array_is_list($playlists)) {
            throw new RuntimeException(
                'playlist.json must contain a JSON array of playlists.'
            );
        }

        /*
         * Validate the complete JSON before modifying the database.
         */
        $validatedPlaylists = [];
        $processedNames = [];

        foreach ($playlists as $index => $playlistData) {
            if (!is_array($playlistData)) {
                throw new RuntimeException(
                    "Playlist at index {$index} must be a JSON object."
                );
            }

            /*
             * Validate playlist name.
             */
            $name = $playlistData['name'] ?? null;

            if (!is_string($name) || trim($name) === '') {
                throw new RuntimeException(
                    "Playlist at index {$index} must have a valid name."
                );
            }

            $name = trim($name);

            $normalizedName = mb_strtolower($name, 'UTF-8');

            /*
             * Playlist names must be unique in the JSON file,
             * regardless of letter case.
             */
            if (isset($processedNames[$normalizedName])) {
                throw new RuntimeException(
                    "Duplicate playlist name found: {$name}"
                );
            }

            $processedNames[$normalizedName] = true;

            /*
             * Validate description.
             * A missing or null description is allowed.
             */
            $description = $playlistData['description'] ?? null;

            if (
                $description !== null
                && !is_string($description)
            ) {
                throw new RuntimeException(
                    "Playlist '{$name}' has an invalid description."
                );
            }

            /*
             * Validate the games array.
             * An empty array is allowed.
             */
            $gameTitles = $playlistData['games'] ?? [];

            if (
                !is_array($gameTitles)
                || !array_is_list($gameTitles)
            ) {
                throw new RuntimeException(
                    "Playlist '{$name}' must have a games array."
                );
            }

            $normalizedTitles = [];
            $cleanGameTitles = [];

            foreach ($gameTitles as $gameTitle) {
                if (
                    !is_string($gameTitle)
                    || trim($gameTitle) === ''
                ) {
                    throw new RuntimeException(
                        "Playlist '{$name}' contains an invalid game title."
                    );
                }

                $gameTitle = trim($gameTitle);

                $normalizedTitle = mb_strtolower(
                    $gameTitle,
                    'UTF-8'
                );

                /*
                 * A game title cannot appear twice in the same playlist.
                 */
                if (isset($normalizedTitles[$normalizedTitle])) {
                    throw new RuntimeException(
                        "Playlist '{$name}' contains duplicate game titles: "
                        . $gameTitle
                    );
                }

                $normalizedTitles[$normalizedTitle] = true;
                $cleanGameTitles[] = $gameTitle;
            }

            /*
             * The number field is not required in JSON.
             * It will be calculated from the matched games.
             */
            $validatedPlaylists[] = [
                'name' => $name,
                'description' => $description,
                'games' => $cleanGameTitles,
            ];
        }

        /*
         * Validate game references and synchronize all playlists
         * inside one transaction.
         *
         * Any exception rolls back all database changes made
         * during this seeder run.
         */
        DB::transaction(function () use ($validatedPlaylists): void {
            foreach ($validatedPlaylists as $playlistData) {
                $name = $playlistData['name'];
                $gameTitles = $playlistData['games'];

                $expectedCount = count($gameTitles);

                /*
                 * Avoid an unfiltered database query when games is empty.
                 */
                if ($expectedCount === 0) {
                    $games = collect();
                } else {
                    /*
                     * Find candidate games by title, ignoring case
                     * and surrounding whitespace.
                     */
                    $games = Game::query()
                        ->where(function ($query) use ($gameTitles): void {
                            foreach ($gameTitles as $title) {
                                $query->orWhereRaw(
                                    'LOWER(TRIM(title)) = ?',
                                    [
                                        mb_strtolower(
                                            $title,
                                            'UTF-8'
                                        ),
                                    ]
                                );
                            }
                        })
                        ->get(['id', 'title']);
                }

                /*
                 * Group database records by normalized title.
                 * This detects duplicate game records.
                 */
                $gamesByNormalizedTitle = [];

                foreach ($games as $game) {
                    $normalizedTitle = mb_strtolower(
                        trim($game->title),
                        'UTF-8'
                    );

                    $gamesByNormalizedTitle[$normalizedTitle][] = $game;
                }

                $missingTitles = [];
                $ambiguousTitles = [];
                $gameIds = [];

                /*
                 * Match every requested title against the database.
                 */
                foreach ($gameTitles as $title) {
                    $normalizedTitle = mb_strtolower(
                        $title,
                        'UTF-8'
                    );

                    $matches = $gamesByNormalizedTitle[
                        $normalizedTitle
                    ] ?? [];

                    if (count($matches) === 0) {
                        $missingTitles[] = $title;
                        continue;
                    }

                    /*
                     * Multiple database records with the same
                     * normalized title are considered ambiguous.
                     */
                    if (count($matches) > 1) {
                        $ambiguousTitles[] = $title;
                        continue;
                    }

                    $gameIds[] = $matches[0]->id;
                }

                /*
                 * Abort the entire transaction if any game is missing
                 * or its title matches multiple database records.
                 */
                if (
                    $missingTitles !== []
                    || $ambiguousTitles !== []
                ) {
                    $lines = [
                        "Playlist '{$name}' validation failed.",
                        "Expected games: {$expectedCount}",
                        'Found unique games: ' . count($gameIds),
                        'Missing games: ' . count($missingTitles),
                        'Ambiguous games: ' . count($ambiguousTitles),
                    ];

                    if ($missingTitles !== []) {
                        $lines[] = 'Missing titles: '
                            . implode(', ', $missingTitles);
                    }

                    if ($ambiguousTitles !== []) {
                        $lines[] = 'Ambiguous titles: '
                            . implode(', ', $ambiguousTitles);
                    }

                    $message = implode("\n", $lines);

                    Log::error($message, [
                        'playlist' => $name,
                        'expected_count' => $expectedCount,
                        'found_unique_count' => count($gameIds),
                        'missing_count' => count($missingTitles),
                        'ambiguous_count' => count($ambiguousTitles),
                        'missing_titles' => $missingTitles,
                        'ambiguous_titles' => $ambiguousTitles,
                    ]);

                    $this->command?->error($message);

                    throw new RuntimeException($message);
                }

                /*
                 * Calculate number automatically.
                 *
                 * number = the actual number of matched games.
                 * No number field is needed in playlist.json.
                 */
                $attributes = [
                    'description' => $playlistData['description'],
                    'number' => count($gameIds),
                ];

                /*
                 * Update an existing playlist by name,
                 * or create a new playlist if it does not exist.
                 *
                 * The database generates the ID.
                 */
                $playlist = Playlist::updateOrCreate(
                    ['name' => $name],
                    $attributes
                );

                /*
                 * Synchronize only this playlist's relationships.
                 *
                 * Old relationships not present in gameIds are removed.
                 * Relationships belonging to other playlists are untouched.
                 */
                $playlist->games()->sync($gameIds);

                $this->command?->info(
                    sprintf(
                        "Playlist '%s' synchronized successfully. Games: %d/%d",
                        $name,
                        count($gameIds),
                        $expectedCount
                    )
                );
            }
        });

        $this->command?->info(
            'All playlists synchronized successfully.'
        );
    }
}