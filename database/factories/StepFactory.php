<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Step>
 */
class StepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "time" => fake()->unixTime,
//            "thumbnail" => "images/meat.jpg",
//            "video_section_time" => fake()->randomNumber(),
//            "animation" => fake()->url,
            "description" => fake()->sentence(),
        ];
    }
}
