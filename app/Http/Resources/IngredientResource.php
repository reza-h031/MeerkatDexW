<?php

namespace App\Http\Resources;

use App\Models\filters\IngredientFilter;
use App\Models\filters\IngredientRelations;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IngredientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ingredientFilters = IngredientFilter::fromArray($request);

        $userIngredients = User::with(["ingredients"])->find(auth()->id())
            ->ingredients()->pluck("ingredient_id");
        $userShoppingList = User::with(["shopping"])->find(auth()->id())
            ->shopping()->pluck("ingredient_id");
        $data = array(
            "id" => $this->id,
            "name" => $this->name,
            "is_in_user_ingredient_list" => $userIngredients->contains(function (int $value, int $key) {
                return $value == $this->id;
            }),
            "is_in_shopping_list" => $userShoppingList->contains(function (int $value, int $key) {
                return $value == $this->id;
            })
        );
        if ($ingredientFilters->getRelations() != null) {
            if ($ingredientFilters->getRelations()->contains(IngredientRelations::Calories->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::All->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::Summary->value)) {
                $data["calories"] = $this->calories;
            }
            if ($ingredientFilters->getRelations()->contains(IngredientRelations::Thumbnail->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::All->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::Summary->value)) {
                $data["thumbnail"] = $this->thumbnail;
            }
            if ($ingredientFilters->getRelations()->contains(IngredientRelations::OriginalImage->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::All->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::Summary->value)) {
                $data["original_image"] = $this->original_image;
            }
            if ($ingredientFilters->getRelations()->contains(IngredientRelations::Category->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::All->value)) {
                $data["category"] = $this->category;
            }
            if ($ingredientFilters->getRelations()->contains(IngredientRelations::Nutritions->value) ||
                $ingredientFilters->getRelations()->contains(IngredientRelations::All->value)) {
                $data["nutritions"] = $this->nutrition;
            }
        }

        if ($this->pivot?->amount) {
            $data["amount"] = $this->pivot->amount;
        }
        if ($this->pivot?->unit) {
            $data["unit"] = $this->pivot->unit;
        }
        if (isset($this->pivot?->is_done)) {
            $data["is_done"] = $this->pivot->is_done;
        }

        return $data;
    }
}
