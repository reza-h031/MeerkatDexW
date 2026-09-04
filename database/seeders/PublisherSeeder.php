<?php

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;

class PublisherSeeder extends Seeder
{
    public function run(): void
    {
        Publisher::create([
            'name' => 'Supercell',
            'logo' => null,
            'website' => 'https://supercell.com',
        ]);

        Publisher::create([
            'name' => 'Krafton',
            'logo' => null,
            'website' => 'https://www.krafton.com',
        ]);

        Publisher::create([
            'name' => 'Mojang Studios',
            'logo' => null,
            'website' => 'https://www.minecraft.net',
        ]);

        Publisher::create([
            'name' => 'ConcernedApe',
            'logo' => null,
            'website' => 'https://www.stardewvalley.net',
        ]);
    }
}