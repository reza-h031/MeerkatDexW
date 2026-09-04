<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    public function run(): void
    {
        Genre::create(['name' => 'Action']);
        Genre::create(['name' => 'Adventure']);
        Genre::create(['name' => 'RPG']);
        Genre::create(['name' => 'Strategy']);
        Genre::create(['name' => 'Simulation']);
        Genre::create(['name' => 'Casual']);
        Genre::create(['name' => 'Puzzle']);
        Genre::create(['name' => 'Sports']);
        Genre::create(['name' => 'Racing']);
        Genre::create(['name' => 'Horror']);
    }
}