<?php

namespace Database\Factories;

use App\Models\Recipe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "status" => fake()->randomElement(["draft", "published"]),
            "name" => fake()->word,
            "rate" => Utils::getRandomRate(),
            "time" => fake()->randomNumber(),
            "calories" => fake()->randomFloat(),
            "image_id" => 1,
            "media_video_id" => 1
        ];
    }

    private function getImages(int $numberOfImages): string
    {
        $images = "";
        for ($i = 0; $i < $numberOfImages; $i++) {
            $images .= fake()->imageUrl . ",";
        }
        return $images;
    }

    private function getVideos(int $numberOfVideos): string
    {
        $videos = "";
        for ($i = 0; $i < $numberOfVideos; $i++) {
            $videos .= fake()->url . ",";
        }
        return $videos;
    }
}
