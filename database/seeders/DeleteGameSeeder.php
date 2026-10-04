<?php

namespace Database\Seeders;

use App\Models\Game;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DeleteGameSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode(
            File::get(database_path('data/delete-game.json')),
            true
        );

        $title = $data['title'] ?? null;

        if (!$title) {
            $this->command->error(
                'Game title is missing from delete-game.json.'
            );

            return;
        }

        $game = Game::where('title', $title)->first();

        if (!$game) {
            $this->command->warn(
                "Game '{$title}' was not found."
            );

            return;
        }

        DB::transaction(function () use ($game) {

            // Remove playlist relationships
            $game->playlists()->detach();

            // Remove platform relationships
            $game->platforms()->detach();

            // Remove genre relationships
            $game->genres()->detach();

            // Remove game images
            $game->images()->delete();

            // Remove game ratings
            $game->ratings()->delete();

            // Remove game
            $game->delete();
        });

        $this->command->info(
            "Game '{$title}' was deleted successfully."
        );
    }
}