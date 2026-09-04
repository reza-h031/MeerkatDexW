<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NutritionFacts>
 */
class NutritionFactsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "fat"=>fake()->randomFloat(),
            "carbs"=>fake()->randomFloat(),
            "protein"=>fake()->randomFloat(),
            "cholesterol"=>fake()->randomFloat(),
            "fiber"=>fake()->randomFloat(),
            "saturated_fat"=>fake()->randomFloat(),
            "sugar"=>fake()->randomFloat(),
        ];
    }
}
