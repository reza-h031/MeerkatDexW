<?php

namespace App\Models\filters;

enum RecipeRelations: string
{
    case Rate = "rate";
    case Time = "time";
    case Calories = "calories";
    case Thumbnail = "thumbnail";
    case OriginalImage = "original_image";
    case Video = "video";
    case Category = "category";
    case Nutrition = "nutrition";
    case Ingredients = "ingredients";
    case Steps = "steps";
    case Tags = "tags";
    case IsFavorite = "is_favorite";
    case Summary = "summary";
    case All = "all";
}
