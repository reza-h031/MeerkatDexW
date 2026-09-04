<?php

namespace App\Models\filters;

use App\Utils\TwoValueIntegerRange;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;

class IngredientFilter
{
    private string $category;
    private string $name;
    private bool $in_user_ingredient_list;
    private bool $in_user_shopping_list;
    private TwoValueIntegerRange $caloriesRange;
    private Collection $relations;

    public function __construct(
        $category = null, $name = null,
        $in_user_ingredient_list = null,
        $in_user_shopping_list = null,
        $caloriesRange = null,
        Collection $relations = null,
    )
    {
        $this->category = $category ?? "";
        $this->name = $name ?? "";
        $this->in_user_ingredient_list = $in_user_ingredient_list ?? false;
        $this->in_user_shopping_list = $in_user_shopping_list ?? false;
        $this->caloriesRange = $caloriesRange ?? new TwoValueIntegerRange();
        $this->relations = $relations ?? new Collection();
    }

    /**
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @param string $category
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return bool
     */
    public function isInUserIngredientList(): bool
    {
        return $this->in_user_ingredient_list;
    }

    /**
     * @param bool $in_user_ingredient
     */
    public function setInUserIngredient(bool $in_user_ingredient_list): void
    {
        $this->in_user_ingredient_list = $in_user_ingredient_list;
    }

    /**
     * @return bool
     */
    public function isInUserShoppingList(): bool
    {
        return $this->in_user_shopping_list;
    }

    /**
     * @param bool $in_user_shopping_list
     */
    public function setInUserShoppingList(bool $in_user_shopping_list): void
    {
        $this->in_user_shopping_list = $in_user_shopping_list;
    }

    public function getCaloriesRange(): TwoValueIntegerRange
    {
        return $this->caloriesRange;
    }

    public function setMinCalories(int $value): void
    {
        $this->caloriesRange->setMin($value);
    }

    public function setMaxCalories(int $value): void
    {
        $this->caloriesRange->setMax($value);
    }

    /**
     * @return Collection
     */
    public function getRelations(): Collection
    {
        return $this->relations;
    }

    /**
     * @param Collection $relations
     */
    public function setRelations(Collection $relations): void
    {
        $this->relations = $relations;
    }

    /**
     * @return bool
     */
    public function isIsDone(): bool
    {
        return $this->is_done;
    }

    /**
     * @param bool $is_done
     */
    public function setIsDone(bool $is_done): void
    {
        $this->is_done = $is_done;
    }

    public function toArray(): array
    {
        return array(
            "category" => $this->category,
            "name" => $this->name,
            "calories_range" => [
                "min" => $this->caloriesRange->getMin(),
                "max" => $this->caloriesRange->getMax(),
            ]
        );
    }

    public static function fromArray(Request $data): IngredientFilter
    {
        $data->validate([
            "name" => "max:50",
            "category" => "max:50",
            "calories_range" => "array",
            "calories_range.min" => "numeric|min:0",
            "calories_range.max" => "numeric",
            "in_user_ingredient_list" => "boolean:strict",
            "in_user_shopping_list" => "boolean:strict",

            "relations" => "array",
            "relations.*" => [new Enum(IngredientRelations::class)],
        ]);

        $caloriesRange = null;
        $relations = null;

        if (isset($data["calories_range"])) {
            // TODO : change TwoValueIntegerRange type to TwoValueFloatRange
            $caloriesRange = new TwoValueIntegerRange(
                $data["calories_range"]["min"] ?? null,
                $data["calories_range"]["max"] ?? null,
            );
        }
        if (isset($data["relations"])) {
            $relations = Collection::make($data["relations"]);
        }

        return new IngredientFilter(
            $data["category"],
            $data["name"],
            $data["in_user_ingredient_list"],
            $data["in_user_shopping_list"],
            $caloriesRange,
            $relations,
        );
    }
}
