<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategorizedIngredients;
use App\Http\Resources\IngredientResource;
use App\Http\Traits\WithSort;
use App\Models\filters\IngredientFilter;
use App\Models\Ingredient;
use Illuminate\Container\Container;
use Illuminate\Http\Request;

class IngredientController extends Controller
{
    use WithSort;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ingredientFilter = IngredientFilter::fromArray($request);
        $sortOption = $this->getSortType($request["sort"], IngredientControllerHelper::SORT_OPTIONS, "name");
        $sortDirection = $this->getSortDirection($request["sort_direction"]);

        return IngredientResource::collection(
            IngredientControllerHelper::getIngredients($ingredientFilter, $sortOption, $sortDirection)
                ->with(IngredientControllerHelper::RELATIONS)
                ->get()
        );
    }

    public function byCategoryIndex(Request $request)
    {
        $ingredientFilter = IngredientFilter::fromArray($request);
        return CategorizedIngredients::collection(
            IngredientControllerHelper::getCategoryPartitionedIngredients($ingredientFilter)
                ->get()
        );
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
//        return Ingredient::with(IngredientControllerHelper::RELATIONS)
//            ->find($id);

        $request = Container::getInstance()->make('request');
        $request["relations"] = ["all"];
        return IngredientResource::make(
            Ingredient::with(IngredientControllerHelper::RELATIONS)->find($id))
            ->resolve($request);
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
