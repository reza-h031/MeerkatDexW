<?php

namespace App\Http\Traits;

trait WithSort
{
    private const SORT_DIRECTIONS = ["asc", "desc"];

    public function getSortType($option, array $options, string $default): string
    {
        return !is_null($option) ? (in_array($option, $options)
            ? $option : $default) : $default;
    }

    public function getSortDirection($option, string $default = "asc")
    {
        return !is_null($option) ?
            (in_array($option, self::SORT_DIRECTIONS) ? $option : $default) : $default;
    }
}
