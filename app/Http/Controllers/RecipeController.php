<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategorizedRecipes;
use App\Http\Resources\RecipeCollection;
use App\Http\Resources\RecipeResource;
use App\Http\Traits\WithSort;
use App\Models\filters\RecipeFilter;
use App\Models\Ingredient;
use App\Models\MediaVariants;
use App\Models\Recipe;
use App\Models\RecipeCategory;
use Awcodes\Curator\Models\Media;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    use WithSort;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $recipeFilter = RecipeFilter::fromArray($request);
        $sortOption = $this->getSortType($request["sort"], RecipeControllerHelper::SORT_OPTIONS, "name");
        $sortDirection = $this->getSortDirection($request["sort_direction"]);

        Paginator::currentPageResolver(function () use ($recipeFilter) {
            return $recipeFilter->getPage();
        });
        $recipes = RecipeControllerHelper::getRecipes(
            $recipeFilter, $sortOption, $sortDirection)->paginate($recipeFilter->getPerPage());

        return new RecipeCollection($recipes);
    }

    public function byCategoryIndex(Request $request)
    {
        $recipeFilter = RecipeFilter::fromArray($request);
        return CategorizedRecipes::collection(
            RecipeControllerHelper::getCategoryPartitionedRecipes($recipeFilter)
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
        $request = Container::getInstance()->make('request');
        $request["relations"] = ["all"];
        return RecipeResource::make(
            Recipe::with(RecipeControllerHelper::RELATIONS)->find($id))
            ->resolve($request);

//        return Recipe::with(RecipeControllerHelper::RELATIONS)->where("id", $id)->get();


//            ->map(function ($v) {
//                $v->thumbnail = MediaVariants::find($v->thumbnail);
//                if (isset($v->category) && isset($v->category->icon)) {
//                    $v->category->icon = url("storage/media/" . $v->category->icon);
//                }
//                return $v;
//            });
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
