<?php

namespace App\Http\Controllers;

use App\Models\filters\IngredientFilter;
use App\Models\Ingredient;
use App\Models\IngredientCategory;
use App\Models\User;

class IngredientControllerHelper
{
    public const SORT_OPTIONS = ["name", "calories"];
    public const RELATIONS = ["category", "nutrition","shopping"];

    public static function getCategoryPartitionedIngredients(IngredientFilter $filter)
    {
        return IngredientCategory::with(
            "ingredients",
        )->whereLike("name", "%" . $filter->getCategory() . "%")
            ->whereHas("ingredients", function ($query)
            use ($filter) {
                $query->whereLike("name", "%" . $filter->getName() . "%")
                    ->whereBetween("calories",
                        [$filter->getCaloriesRange()->getMin(), $filter->getCaloriesRange()->getMax()]);
            })
            ->when($filter->isInUserIngredientList(), function ($query) {
                $userIngredients = self::getUserIngredients();
                $query->whereHas("ingredients", fn($q) => $q->whereIn("id", $userIngredients))
                    ->with(["ingredients" => fn($q) => $q->whereIn("id", $userIngredients)]);
            })
            ->when($filter->isInUserShoppingList(), function ($query) {
                $userShoppingList = self::getUserShoppingList();
                $query->whereHas("ingredients", fn($q) => $q->whereIn("id", $userShoppingList))
                    ->with(["ingredients" => fn($q) => $q->whereIn("id", $userShoppingList)]);
            })
            ->orderBy("name");
    }

    public static function getIngredients(IngredientFilter $filter, $sortOption, $sortDirection)
    {
        return Ingredient::query()
            ->whereLike("ingredients.name", "%" . $filter->getName() . "%")
            ->whereBetween("calories",
                [$filter->getCaloriesRange()->getMin(), $filter->getCaloriesRange()->getMax()])
            ->when($filter->isInUserIngredientList(),
                fn($q) => $q->whereIn("ingredients.id", self::getUserIngredients()->values()))
            ->when($filter->isInUserShoppingList(),
                fn($q) => $q->whereIn("ingredients.id", self::getUserShoppingList()->values()))
            ->orderBy($sortOption, $sortDirection)
            ->select("ingredients.*");
    }

    public static function getUserIngredients()
    {
        return User::with(["ingredients"])->find(auth()->id())
            ->ingredients()->pluck("ingredient_id");
    }

    public static function getUserShoppingList()
    {
        return User::with(["shopping"])->find(auth()->id())
            ->shopping()->pluck("ingredient_id");
    }
}
