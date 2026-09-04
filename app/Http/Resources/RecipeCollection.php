<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class RecipeCollection extends ResourceCollection
{
    public $collects = RecipeResource::class;

    public function toArray(Request $request)
    {
        return [
            "data" => $this->collection,
        ];
    }

    public function with(Request $request)
    {
        return [
            "from" => $this->resource->firstItem(),
            "to" => $this->resource->lastItem(),
            "pages" => $this->resource->lastPage(),
            "total" => $this->resource->total()
        ];
    }

    public function toResponse($request)
    {
        return response()->json(
            array_merge(
                $this->toArray($request),
                $this->with($request)
            )
        );
    }
}
