<?php

namespace App\Models\filters;

use Illuminate\Http\Request;

class ShoppingFilter
{
    private IngredientFilter $ingredientFilter;
    private string $is_done;

    public function __construct(
        IngredientFilter $ingredientFilter = null,
        string           $is_done = null,
    )
    {
        $this->ingredientFilter = $ingredientFilter;
        $this->is_done = $is_done ?? "all";
    }

    /**
     * @return IngredientFilter
     */
    public function getIngredientFilter(): IngredientFilter
    {
        return $this->ingredientFilter;
    }

    /**
     * @param IngredientFilter $ingredientFilter
     */
    public function setIngredientFilter(IngredientFilter $ingredientFilter): void
    {
        $this->ingredientFilter = $ingredientFilter;
    }

    /**
     * @return string
     */
    public function getIsDone(): string
    {
        return $this->is_done;
    }

    /**
     * @param string $is_done
     */
    public function setIsDone(string $is_done): void
    {
        $this->is_done = $is_done;
    }

    public static function fromArray(Request $request): ShoppingFilter
    {
        $request->validate([
            "is_done" => ["string", "in:done,undone,all"]
        ]);

        $ingredientFilter = IngredientFilter::fromArray($request);

        return new ShoppingFilter(
            $ingredientFilter,
            $request["is_done"]
        );
    }
}
