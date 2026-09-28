<?php

namespace App\Models;

class SimpleGame
{
    public int $id;
    public string $title;
    public $image_cover;
    public $image_icon;
    public $ratings;
    public $genres;
    public $platforms;

    public function __construct(
        int $id,
        string $title,
        $image_cover,
        $image_icon,
        $ratings,
        $genres,
        $platforms
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->image_cover = $image_cover;
        $this->image_icon = $image_icon;
        $this->ratings = $ratings;
        $this->genres = $genres;
        $this->platforms = $platforms;
    }
}