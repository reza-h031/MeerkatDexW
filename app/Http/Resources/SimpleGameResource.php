<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleGameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $cover = $this->images
            ->firstWhere('type', 'cover');

        $icon = $this->images
            ->firstWhere('type', 'icon');

        return [
            'id' => $this->id,

            'title' => $this->title,

            'image_cover' => $cover?->path,

            'image_icon' => $icon?->path,

            'ratings' => $this->ratings->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'source' => $rating->source,
                    'logoSource' => $rating->logo_source,
                    'rating' => (string) $rating->rating,
                    'ratingCount' => (string) $rating->rating_count,
                ];
            }),

            'genres' => $this->genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ];
            }),

            'platforms' => $this->platforms->map(function ($platform) {
                return [
                    'id' => $platform->id,
                    'name' => $platform->name,
                    'logo' => $platform->logo,
                ];
            }),
        ];
    }
}