<?php

namespace Database\Seeders;

use App\Models\MediaVariants;
use App\Models\User;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(MediaSeeder::class);
        $this->call(MediaVariantsSeeder::class);
        $this->call([
    DeveloperSeeder::class,
    PublisherSeeder::class,
    PlatformSeeder::class,
    GenreSeeder::class,
    GameSeeder::class,
    PlaylistSeeder::class,
        ]);

    }
}
