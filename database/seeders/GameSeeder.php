<?php

namespace Database\Seeders;

use App\Models\Game;
use App\Models\Developer;
use App\Models\Publisher;
use App\Models\Platform;
use App\Models\Genre;
use Illuminate\Database\Seeder;
use App\Models\Requirement;
use App\Models\GameRequirement;
class GameSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Brawl Stars
        |--------------------------------------------------------------------------
        */

        $brawlStars = Game::create([
            'title' => 'Brawl Stars',
            'description' => 'A multiplayer action game with fast-paced battles and different game modes.',
            'release_date' => '2018-12-12',
            'developer_id' => Developer::where('name', 'Supercell')->first()->id,
            'publisher_id' => Publisher::where('name', 'Supercell')->first()->id,
            'website' => 'https://supercell.com/en/games/brawlstars/',
            'status' => 'released',
        ]);
$minimumRequirement = Requirement::create([
    'ram' => '2 GB',
    'system_version' => 'Android 7.0',
    'cpu' => 'Snapdragon 450',
    'gpu' => 'Adreno 506',
    'storage' => '1 GB',
]);

$recommendedRequirement = Requirement::create([
    'ram' => '4 GB',
    'system_version' => 'Android 10.0',
    'cpu' => 'Snapdragon 660',
    'gpu' => 'Adreno 512',
    'storage' => '2 GB',
]);
$requirement = GameRequirement::create([
    'game_id' => $brawlStars->id,
    'minimum_requirement_id' => $minimumRequirement->id,
    'recommended_requirement_id' => $recommendedRequirement->id,
]);
$android = Platform::where('name', 'Android')->first();
$ios = Platform::where('name', 'iOS')->first();

$brawlStars->platforms()->attach([
    $android->id => [
        'version' => '61.249',
        'release_date' => '2026-08-20',
        'download_size' => '250 MB',
        'game_requirement_id' => $requirement->id,
    ],

    $ios->id => [
        'version' => '61.249',
        'release_date' => '2026-08-20',
        'download_size' => '250 MB',
        'game_requirement_id' => $requirement->id,
    ],
]);

$brawlStars->genres()->attach([
    Genre::where('name', 'Action')->first()->id,
]);

// Images
$brawlStars->images()->createMany([
    [
        'path' => 'games/brawl-stars/cover.jpg',
        'type' => 'cover',
    ],
    [
        'path' => 'games/brawl-stars/screenshot-1.jpg',
        'type' => 'screenshot',
    ],
    [
        'path' => 'games/brawl-stars/screenshot-2.jpg',
        'type' => 'screenshot',
    ],
]);


// Ratings
$brawlStars->ratings()->create([
    'source' => 'Google Play',
    'logo_source' => 'ratings/google-play.png',
    'rating' => 4.5,
    'rating_count' => 12500000,
]);


        /*
        |--------------------------------------------------------------------------
        | PUBG Mobile
        |--------------------------------------------------------------------------
        */

        $pubgMobile = Game::create([
            'title' => 'PUBG Mobile',
            'description' => 'A battle royale game where players compete to survive and become the last player standing.',
            'release_date' => '2018-03-19',
            'developer_id' => Developer::where('name', 'Krafton')->first()->id,
            'publisher_id' => Publisher::where('name', 'Krafton')->first()->id,
            'website' => 'https://www.pubgmobile.com/',
            'status' => 'released',
        ]);

        $pubgMobile->platforms()->attach([
            Platform::where('name', 'Android')->first()->id,
            Platform::where('name', 'iOS')->first()->id,
        ]);

        $pubgMobile->genres()->attach([
            Genre::where('name', 'Action')->first()->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Minecraft
        |--------------------------------------------------------------------------
        */

        $minecraft = Game::create([
            'title' => 'Minecraft',
            'description' => 'A sandbox game focused on exploration, building, crafting and survival.',
            'release_date' => '2011-11-18',
            'developer_id' => Developer::where('name', 'Mojang Studios')->first()->id,
            'publisher_id' => Publisher::where('name', 'Mojang Studios')->first()->id,
            'website' => 'https://www.minecraft.net/',
            'status' => 'released',
        ]);

        $minecraft->platforms()->attach([
            Platform::where('name', 'Android')->first()->id,
            Platform::where('name', 'iOS')->first()->id,
            Platform::where('name', 'Windows')->first()->id,
            Platform::where('name', 'macOS')->first()->id,
            Platform::where('name', 'Linux')->first()->id,
        ]);

        $minecraft->genres()->attach([
            Genre::where('name', 'Adventure')->first()->id,
            Genre::where('name', 'Simulation')->first()->id,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Stardew Valley
        |--------------------------------------------------------------------------
        */

        $stardewValley = Game::create([
            'title' => 'Stardew Valley',
            'description' => 'A farming simulation RPG where players build a farm, explore the world and interact with its characters.',
            'release_date' => '2016-02-26',
            'developer_id' => Developer::where('name', 'ConcernedApe')->first()->id,
            'publisher_id' => Publisher::where('name', 'ConcernedApe')->first()->id,
            'website' => 'https://www.stardewvalley.net/',
            'status' => 'released',
        ]);

        $stardewValley->platforms()->attach([
            Platform::where('name', 'Android')->first()->id,
            Platform::where('name', 'iOS')->first()->id,
            Platform::where('name', 'Windows')->first()->id,
            Platform::where('name', 'macOS')->first()->id,
            Platform::where('name', 'Linux')->first()->id,
            Platform::where('name', 'PlayStation 5')->first()->id,
            Platform::where('name', 'Xbox Series X|S')->first()->id,
        ]);

        $stardewValley->genres()->attach([
            Genre::where('name', 'RPG')->first()->id,
            Genre::where('name', 'Simulation')->first()->id,
        ]);
    }
}