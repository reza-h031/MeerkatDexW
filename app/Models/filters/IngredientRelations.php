<?php

namespace App\Models\filters;

enum IngredientRelations: string
{
    case Calories = "calories";
    case Thumbnail = "thumbnail";
    case OriginalImage = "original_image";
    case Category = "category";
    case Nutritions = "nutritions";

    case Summary = "summary";
    case All = "all";
}
