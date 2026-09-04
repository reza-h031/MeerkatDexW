<?php

namespace App\Models\filters;

use App\Utils\TwoValueFloatRange;
use App\Utils\TwoValueIntegerRange;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\Enum;

class RecipeFilter
{
    private string $category;
    private string $name;
    private TwoValueFloatRange $rateRange;
    private TwoValueIntegerRange $timeRange;
    private TwoValueFloatRange $caloriesRange;
    private bool $isFavorite;
    private bool $areIngredientsAvailable;
    private Collection $relations;
    private int $perPage;
    private int $page;
    private int $from;
    private int $to;

    public function __construct(
        string               $category = null,
        string               $name = null,
        TwoValueFloatRange   $rateRange = null,
        TwoValueIntegerRange $timeRange = null,
        TwoValueFloatRange   $caloriesRange = null,
        bool                 $isFavorite = false,
        bool                 $areIngredientsAvailable = false,
        Collection           $relations = null,
        int                  $perPage = null,
        int                  $page = null,
        int                  $from = null,
        int                  $to = null,
    )
    {
        $this->category = $category ?? "";
        $this->name = $name ?? "";
        $this->rateRange = $rateRange ?? new TwoValueFloatRange();
        $this->timeRange = $timeRange ?? new TwoValueIntegerRange();
        $this->caloriesRange = $caloriesRange ?? new TwoValueFloatRange();
        $this->isFavorite = $isFavorite ?? false;
        $this->areIngredientsAvailable = $areIngredientsAvailable ?? false;
        $this->relations = $relations ?? new Collection();
        $this->perPage = $perPage ?? 3;
        $this->page = $page ?? 1;
        $this->from = $from ?? 1;
        $this->to = $to ?? 10;
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
     * @return TwoValueFloatRange
     */
    public function getRateRange(): TwoValueFloatRange
    {
        return $this->rateRange;
    }

    /**
     * @param TwoValueFloatRange $rateRange
     */
    public function setRateRange(TwoValueFloatRange $rateRange): void
    {
        $this->rateRange = $rateRange;
    }

    /**
     * @return TwoValueIntegerRange
     */
    public function getTimeRange(): TwoValueIntegerRange
    {
        return $this->timeRange;
    }

    /**
     * @param TwoValueIntegerRange $timeRange
     */
    public function setTimeRange(TwoValueIntegerRange $timeRange): void
    {
        $this->timeRange = $timeRange;
    }

    /**
     * @return TwoValueFloatRange
     */
    public function getCaloriesRange(): TwoValueFloatRange
    {
        return $this->caloriesRange;
    }

    /**
     * @param TwoValueFloatRange $caloriesRange
     */
    public function setCaloriesRange(TwoValueFloatRange $caloriesRange): void
    {
        $this->caloriesRange = $caloriesRange;
    }

    /**
     * @return bool
     */
    public function isFavorite(): bool
    {
        return $this->isFavorite;
    }

    /**
     * @param bool $isFavorite
     */
    public function setIsFavorite(bool $isFavorite): void
    {
        $this->isFavorite = $isFavorite;
    }

    /**
     * @return bool
     */
    public function areIngredientsAvailable(): bool
    {
        return $this->areIngredientsAvailable;
    }

    /**
     * @param bool $areIngredientsAvailable
     */
    public function setAreIngredientsAvailable(bool $areIngredientsAvailable): void
    {
        $this->areIngredientsAvailable = $areIngredientsAvailable;
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
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @param int $perPage
     */
    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    /**
     * @return int
     */
    public function getFrom(): int
    {
        return $this->from;
    }

    /**
     * @param int $from
     */
    public function setFrom(int $from): void
    {
        $this->from = $from;
    }

    /**
     * @return int
     */
    public function getTo(): int
    {
        return $this->to;
    }

    /**
     * @param int $to
     */
    public function setTo(int $to): void
    {
        $this->to = $to;
    }

    public function toArray(): array
    {
        return array(
            "category" => $this->category,
            "name" => $this->name,
            "rate_range" => [
                "min" => $this->rateRange->getMin(),
                "max" => $this->rateRange->getMax(),
            ],
            "time_range" => [
                "min" => $this->timeRange->getMin(),
                "max" => $this->timeRange->getMax(),
            ],
            "calories_range" => [
                "min" => $this->caloriesRange->getMin(),
                "max" => $this->caloriesRange->getMax(),
            ],
            "is_favorite" => $this->isFavorite
        );
    }

    public static function fromArray(Request $request): RecipeFilter
    {
        $request->validate([
            "name" => "max:50",
            "category" => "max:50",

            "rate_range" => "array",
            "rate_range.min" => "numeric|min:0",
            "rate_range.max" => "numeric|max:5",

            "calories_range" => "array",
            "calories_range.min" => "numeric|min:0",
            "calories_range.max" => "numeric",

            "time_range" => "array",
            "time_range.min" => "integer|min:0",
            "time_range.max" => "integer",

            "is_favorite" => "boolean:strict",
            "are_ingredients_available" => "boolean:strict",

            "relations" => "array",
            "relations.*" => [new Enum(RecipeRelations::class)],

            "per_page" => "integer",
            "page" => "integer",
            "from" => "integer",
            "to" => "integer",
        ]);

        $rateRange = null;
        $timeRange = null;
        $caloriesRange = null;
        $isFavorite = false;
        $areIngredientsAvailable = false;
        $relations = null;

        if (isset($request["rate_range"])) {
            $rateRange = new TwoValueFloatRange(
                $request["rate_range"]["min"] ?? null,
                $request["rate_range"]["max"] ?? null
            );
        }
        if (isset($request["time_range"])) {
            $timeRange = new TwoValueIntegerRange(
                $request["time_range"]["min"] ?? null,
                $request["time_range"]["max"] ?? null
            );
        }
        if (isset($request["calories_range"])) {
            $caloriesRange = new TwoValueFloatRange(
                $request["calories_range"]["min"] ?? null,
                $request["calories_range"]["max"] ?? null
            );
        }
        if (isset($request["is_favorite"])) {
            $isFavorite = $request["is_favorite"];
        }
        if (isset($request["are_ingredients_available"])) {
            $areIngredientsAvailable = $request["are_ingredients_available"];
        }
        if (isset($request["relations"])) {
            $relations = Collection::make($request["relations"]);
        }

        return new RecipeFilter(
            $request["category"],
            $request["name"],
            $rateRange,
            $timeRange,
            $caloriesRange,
            $isFavorite,
            $areIngredientsAvailable,
            $relations,
            $request["per_page"],
            $request["page"],
            $request["from"],
            $request["to"],
        );
    }
}
