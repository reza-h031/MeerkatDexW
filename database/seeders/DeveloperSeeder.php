<?php

namespace Database\Seeders;

use App\Models\Developer;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        Developer::create([
            'name' => 'Supercell',
            'logo' => null,
            'website' => 'https://supercell.com',
        ]);

        Developer::create([
            'name' => 'Krafton',
            'logo' => null,
            'website' => 'https://www.krafton.com',
        ]);

        Developer::create([
            'name' => 'Mojang Studios',
            'logo' => null,
            'website' => 'https://www.minecraft.net',
        ]);

        Developer::create([
            'name' => 'ConcernedApe',
            'logo' => null,
            'website' => 'https://www.stardewvalley.net',
        ]);
    }
}