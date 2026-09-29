<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleGameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,

            'image_cover' => $this->image_cover,
            'image_icon' => $this->image_icon,

            'ratings' => $this->ratings,

            'genres' => $this->genres,

            'platforms' => $this->platforms,
        ];
    }
}