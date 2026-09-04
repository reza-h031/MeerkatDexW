<?php

namespace App\Http\Resources;

use App\Models\filters\RecipeFilter;
use App\Models\filters\RecipeRelations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RecipeResource extends JsonResource
{
    public static array $relations;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $recipeFilters = RecipeFilter::fromArray($request);

        $userFavoriteRecipes = User::with(["favoriteRecipes"])->find(auth()->id())
            ->favoriteRecipes()->pluck("recipe_id");
        $data = array(
            "id" => $this->id,
            "name" => $this->name,
        );
        if ($recipeFilters->getRelations() != null) {
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Rate->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["rate"] = $this->rate;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Time->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["time"] = $this->time;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Calories->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["calories"] = $this->calories;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Thumbnail->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["thumbnail"] = $this->thumbnail;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::OriginalImage->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["original_image"] = $this->original_image;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Video->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["video"] = $this->video;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Category->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["category"] = $this->category;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Nutrition->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value)) {
                $data["nutrition"] = $this->nutrition;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Ingredients->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value)) {
                $data["ingredients"] = IngredientResource::collection($this->ingredients);
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Steps->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value)) {
                $data["steps"] = StepResource::collection($this->steps);
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::Tags->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value)) {
                $data["tags"] = $this->tags;
            }
            if ($recipeFilters->getRelations()->contains(RecipeRelations::IsFavorite->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::All->value) ||
                $recipeFilters->getRelations()->contains(RecipeRelations::Summary->value)) {
                $data["is_favorite"] = $userFavoriteRecipes->contains(
                    fn($value) => $this->id === $value);
            }
        }

        return $data;
    }
}
