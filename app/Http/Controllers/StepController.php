<?php

namespace App\Http\Controllers;

use App\Http\Resources\StepResource;
use App\Models\Step;
use Illuminate\Http\Request;

class StepController extends Controller
{
    public const RELATIONS = ["images", "videos"];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $searchTerm = isset($request["search"]) ? $request["search"] : "";
        return StepResource::collection(
            Step::with(self::RELATIONS)
                ->whereLike("description", "%$searchTerm%")->get()
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
        return StepResource::make(
            Step::query()->find($id)
        );
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
