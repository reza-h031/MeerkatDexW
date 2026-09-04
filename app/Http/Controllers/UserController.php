<?php

namespace App\Http\Controllers;

use App\Http\Resources\IngredientResource;
use App\Http\Resources\RecipeResource;
use App\Http\Traits\WithSort;
use App\Models\filters\IngredientFilter;
use App\Models\filters\RecipeFilter;
use App\Models\filters\ShoppingFilter;
use App\Models\Ingredient;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use WithSort;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // some data
    }

    public function favoriteRecipes(Request $request)
    {
        $recipeFilter = RecipeFilter::fromArray($request);
        $sortOption = $this->getSortType($request["sort"], RecipeControllerHelper::SORT_OPTIONS, "name");
        $sortDirection = $this->getSortDirection($request["sort_direction"]);

        return RecipeResource::collection(
            RecipeControllerHelper::getRecipes($recipeFilter, $sortOption, $sortDirection)
                ->with(RecipeControllerHelper::RELATIONS)
                ->get()
        );
    }

    public function insertRecipeToFavorites(int $recipeId)
    {
        User::with("favoriteRecipes")->find(auth()->id())->favoriteRecipes()
            ->syncWithoutDetaching($recipeId);

        return response()->json([
            "message" => "Recipe successfully added to favorites"
        ], status: 201);
    }

    public function deleteRecipeFromFavorites(int $recipeId)
    {
        User::with("favoriteRecipes")->find(auth()->id())->favoriteRecipes()
            ->detach($recipeId);

        return response()->json([
            "message" => "Recipe successfully removed from favorites"
        ]);
    }

    public function ingredients(Request $request)
    {
        $ingredientFilter = IngredientFilter::fromArray($request);
        $sortOption = $this->getSortType($request["sort"], IngredientControllerHelper::SORT_OPTIONS, "name");
        $sortDirection = $this->getSortDirection($request["sort_direction"]);

        if ($sortOption === "category") {
            return IngredientControllerHelper::getCategoryPartitionedIngredients($ingredientFilter)
                ->whereHas("ingredients.users", function ($query) {
                    $query->where("users.id", auth()->id());
                })
                ->with(["ingredients" => function ($query) {
                    $query->whereHas("users", function ($query) {
                        $query->where("users.id", auth()->id());
                    });
                }])
                ->get();
        } else {
            return IngredientControllerHelper::getIngredients($ingredientFilter, $sortOption, $sortDirection)
                ->with(["count"])
                ->whereHas("users", function ($query) {
                    $query->where("users.id", auth()->id());
                })->get();
        }
    }

    public function shopping(Request $request)
    {
        $shoppingFilter = ShoppingFilter::fromArray($request);
//        $sortOption = $this->getSortType($request["sort"], IngredientControllerHelper::SORT_OPTIONS, "name");
//        $sortDirection = $this->getSortDirection($request["sort_direction"]);


        return IngredientResource::collection(
            User::with(["shopping"])->find(auth()->id())->shopping
                ->when($shoppingFilter->getIsDone() != "all", function ($query) use ($shoppingFilter) {
                    $isDone = $shoppingFilter->getIsDone() === "done";
                    return $query->where("is_done", $isDone);
                })
        );
    }

    public function insertUserIngredients(Request $request)
    {
        $ingredientIds = $request["ingredients"];
        if (is_null($ingredientIds) || sizeof($ingredientIds) <= 0) {
            response()->json([
                "message" => "You have to insert at least an ingredient id"
            ], 403);
        }
        User::with(["ingredients"])->find(auth()->id())->ingredients()->syncWithoutDetaching(
            $ingredientIds
        );

        return response()->json([
            "message" => "Ingredients successfully inserted"
        ], status: 201);
    }

    public function insertUserIngredient(int $ingredientId)
    {
        User::with(["ingredients"])->find(auth()->id())->ingredients()
            ->syncWithoutDetaching($ingredientId);

        return response()->json([
            "message" => "Ingredient successfully inserted"
        ], status: 201);
    }

    public function deleteUserIngredient(int $ingredientId)
    {
        User::with(["ingredients"])->find(auth()->id())->ingredients()
            ->detach($ingredientId);

        return response()->json([
            "message" => "Ingredient successfully removed"
        ]);
    }

    public function clearUserIngredientList()
    {
        User::with(["ingredients"])->find(auth()->id())->ingredients()->detach();

        return response()->json([
            "message" => "User's ingredient list successfully cleared"
        ]);
    }

    public function insertShoppingIngredient(int $ingredientId)
    {
        User::with(["shopping"])->find(auth()->id())->shopping()
            ->syncWithoutDetaching($ingredientId);

        return response()->json([
            "message" => "Shopping ingredient successfully inserted"
        ], status: 201);
    }

    public function deleteShoppingIngredient(int $ingredientId)
    {
        User::with(["shopping"])->find(auth()->id())->shopping()->detach($ingredientId);

        return response()->json([
            "message" => "Shopping ingredient successfully deleted"
        ]);
    }

    public function clearUserShoppingList()
    {
        User::with(["shopping"])->find(auth()->id())->shopping()->detach();

        return response()->json([
            "message" => "User's shopping list successfully cleared"
        ]);
    }

    public function addToFavorite(Request $request)
    {
        $recipeIds = $request["recipes"];
        if (is_null($recipeIds) || sizeof($recipeIds) <= 0) {
            abort(403, "You have to insert at least a recipe id");
        }
        User::with(["favoriteRecipes"])->find(auth()->id())->favoriteRecipes()
            ->syncWithoutDetaching($recipeIds);

        return response()->json([
            "message" => "Recipe successfully added to favorites"
        ]);
    }

    public function changeUserShoppingIngredientCompletion(int $ingredientId, Request $request)
    {
        $isDone = false;
        if (isset($request["is_done"])) {
            $isDone = $request["is_done"];
        }
        User::with(["shopping"])->find(auth()->id())->shopping()
            ->where("ingredient_id", $ingredientId)->update([
                "is_done" => $isDone
            ]);

        return response()->json([
            "message" => "The ingredient in shopping list completion changed successfully"
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
