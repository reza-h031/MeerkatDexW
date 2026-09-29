<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Playlist;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        $playlists = json_decode(
            File::get(database_path('data/playlist.json')),
            true
        );

        foreach ($playlists as $playlistData) {

            $playlist = Playlist::updateOrCreate(
                [
                    'name' => $playlistData['name'],
                ],
                [
                    'description' => $playlistData['description'] ?? null,
                ]
            );

            $gameIds = Game::whereIn(
                'title',
                $playlistData['games'] ?? []
            )->pluck('id');

            $playlist->games()->sync($gameIds);
        }
    }
}