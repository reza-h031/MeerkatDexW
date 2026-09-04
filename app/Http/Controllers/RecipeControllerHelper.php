<?php

namespace App\Http\Controllers;

use App\Models\filters\RecipeFilter;
use App\Models\Ingredient;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use App\Models\User;

class RecipeControllerHelper
{
    public const SORT_OPTIONS = ["name", "rate", "time", "calories"];
    public const RELATIONS = ["category", "nutrition", "ingredients", "steps",
        "steps.images", "steps.videos", "tags"];

    public static function getCategoryPartitionedRecipes(RecipeFilter $filter)
    {
        return RecipeCategory::with(
            "recipes",
        )->whereLike("name", "%" . $filter->getCategory() . "%")
            ->whereHas("recipes", function ($query) use ($filter) {
                $query
                    ->whereLike("name", "%" . $filter->getName() . "%")
                    ->whereBetween("rate",
                        [$filter->getRateRange()->getMin(), $filter->getRateRange()->getMax()])
                    ->whereBetween("time",
                        [$filter->getTimeRange()->getMin(), $filter->getTimeRange()->getMax()])
                    ->whereBetween("calories",
                        [$filter->getCaloriesRange()->getMin(), $filter->getCaloriesRange()->getMax()]);
            })
            ->when($filter->isFavorite(),
                function ($query) {
                    $favoriteRecipeIds = self::getFavoriteRecipes();
                    $query->whereHas("recipes", fn($q) => $q->whereIn("id", $favoriteRecipeIds))
                        ->with(["recipes" => fn($q) => $q->whereIn("id", $favoriteRecipeIds)]);
                })
            ->when($filter->areIngredientsAvailable(), fn($q) => $q->whereHas("recipes",
                fn($q) => $q->whereDoesntHave("ingredients", fn($q) => $q->whereNotIn("ingredients.id", self::getUserIngredients()))

            )->with(["recipes" => fn($q) => $q->whereDoesntHave("ingredients", fn($q) => $q->whereNotIn("ingredients.id", self::getUserIngredients()))])
            )
            ->orderBy("name");
    }

    public static function getRecipes(
        RecipeFilter $filter, $sortOption, $sortDirection,
    )
    {
        $favoriteRecipeIds = self::getFavoriteRecipes();

        return Recipe::query()
            ->when($filter->isFavorite(),
                fn($q) => $q->whereIn("recipes.id", $favoriteRecipeIds->values()))
            ->whereLike("recipes.name", "%" . $filter->getName() . "%")
            ->whereBetween("rate",
                [$filter->getRateRange()->getMin(), $filter->getRateRange()->getMax()])
            ->whereBetween("time",
                [$filter->getTimeRange()->getMin(), $filter->getTimeRange()->getMax()])
            ->whereBetween("calories",
                [$filter->getCaloriesRange()->getMin(), $filter->getCaloriesRange()->getMax()])
            ->when($filter->areIngredientsAvailable(),
                fn($q) => $q->whereDoesntHave("ingredients", fn($q) => $q->whereNotIn("ingredients.id", self::getUserIngredients()))
//                    ->with(["ingredients" => fn($q) => $q->whereIn("ingredients.id", self::getUserIngredients())])
            )
            ->orderBy($sortOption, $sortDirection)
            ->select("recipes.*");


//        return Collection::make([
//            "count" => $recipes->count(),
//            $recipes
//        ]);
    }

    public static function getFavoriteRecipes()
    {
        return User::with(["favoriteRecipes"])->find(auth()->id())
            ->favoriteRecipes()->pluck("recipe_id");
    }

    public static function getUserIngredients()
    {
        return User::with(["ingredients"])->find(auth()->id())
            ->ingredients()->pluck("ingredient_id");
    }
}
