<?php

namespace Database\Seeders;

use App\Models\Platform;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        Platform::create([
            'name' => 'Android',
            'logo' => null,
        ]);

        Platform::create([
            'name' => 'iOS',
            'logo' => null,
        ]);

        Platform::create([
            'name' => 'Windows',
            'logo' => null,
        ]);

        Platform::create([
            'name' => 'macOS',
            'logo' => null,
        ]);

        Platform::create([
            'name' => 'Linux',
            'logo' => null,
        ]);

        Platform::create([
            'name' => 'PlayStation 5',
            'logo' => null,
        ]);

        Platform::create([
            'name' => 'Xbox Series X|S',
            'logo' => null,
        ]);
    }
}