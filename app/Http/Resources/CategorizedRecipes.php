<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategorizedRecipes extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = array(
            "id" => $this->id,
            "name" => $this->name,
            "thumbnail" => $this->thumbnail,
            "original_image" => $this->original_image,
            "recipes" => RecipeResource::collection($this->recipes)
        );

        return $data;
    }
}
