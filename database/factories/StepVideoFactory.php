<?php

namespace Database\Factories;

use App\Models\StepVideo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StepVideo>
 */
class StepVideoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "media_id" => 1
//            "name" => fake()->word(),
//            "duration" => 22,
//            "address" => "videos/video.mp4"
        ];
    }
}
