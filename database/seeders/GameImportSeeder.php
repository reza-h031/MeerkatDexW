<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Developer;
use App\Models\Publisher;
use App\Models\Platform;
use App\Models\Genre;
use App\Models\Requirement;
use App\Models\GameRequirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class GameImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('data/newGame.json');

        if (!File::exists($path)) {
            $this->command->error('newGame.json not found.');

            return;
        }

        $games = json_decode(
            File::get($path),
            true
        );

        if (!is_array($games)) {
            $this->command->error('Invalid JSON format.');

            return;
        }

        foreach ($games as $gameData) {

            /*
             * Developer
             */
            $developer = Developer::updateOrCreate(
                [
                    'name' => $gameData['developer']['name'],
                ],
                [
                    'logo' => $gameData['developer']['logo'] ?? null,
                    'website' => $gameData['developer']['website'] ?? null,
                ]
            );

            /*
             * Publisher
             */
            $publisher = Publisher::updateOrCreate(
                [
                    'name' => $gameData['publisher']['name'],
                ],
                [
                    'logo' => $gameData['publisher']['logo'] ?? null,
                    'website' => $gameData['publisher']['website'] ?? null,
                ]
            );

            /*
             * Game
             */
            $game = Game::create([
                'title' => $gameData['title'],
                'description' => $gameData['description'],
                'release_date' => $gameData['release_date'] ?? null,
                'developer_id' => $developer->id,
                'publisher_id' => $publisher->id,
                'website' => $gameData['website'] ?? null,
                'status' => $gameData['status'] ?? null,
            ]);

            /*
             * Genres
             */
            foreach ($gameData['genres'] ?? [] as $genreName) {

                $genre = Genre::firstOrCreate([
                    'name' => $genreName,
                ]);

                $game->genres()->attach($genre->id);
            }

            /*
             * Platforms
             */
            foreach ($gameData['platforms'] ?? [] as $platformData) {

                $platform = Platform::updateOrCreate(
                    [
                        'name' => $platformData['name'],
                    ],
                    [
                        'logo' => $platformData['logo'] ?? null,
                    ]
                );

                $gameRequirement = null;

                /*
                 * Requirements
                 */
                if (isset($platformData['requirement'])) {

                    $minimum = Requirement::create(
                        $platformData['requirement']['minimum']
                    );

                    $recommended = Requirement::create(
                        $platformData['requirement']['recommended']
                    );

                    $gameRequirement = GameRequirement::create([
                        'minimum_requirement_id' => $minimum->id,
                        'recommended_requirement_id' => $recommended->id,
                    ]);
                }

                /*
                 * Game Platform
                 */
                $game->platforms()->attach(
                    $platform->id,
                    [
                        'version' => $platformData['version'] ?? null,
                        'release_date' => $platformData['release_date'] ?? null,
                        'download_size' => $platformData['download_size'] ?? null,
                        'game_requirement_id' => $gameRequirement?->id,
                    ]
                );
            }

            /*
             * Images
             */
            foreach ($gameData['images'] ?? [] as $index => $path) {

                $type = match ($index) {
                    0 => 'icon',
                    1 => 'cover',
                    default => 'screenshot',
                };

                $game->images()->create([
                    'path' => $path,
                    'type' => $type,
                ]);
            }

            /*
             * Ratings
             */
            foreach ($gameData['ratings'] ?? [] as $rating) {

                $game->ratings()->create([
                    'source' => $rating['source'],
                    'logo_source' => $rating['logo_source'] ?? null,
                    'rating' => $rating['rating'],
                    'rating_count' => $rating['rating_count'],
                ]);
            }

            $this->command->info(
                "Game '{$game->title}' added successfully."
            );
        }
    }
}