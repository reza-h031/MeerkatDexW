<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'title' => $this->title,

            'description' => $this->description,

            'releaseDate' => $this->release_date,

            'developer' => [
                'id' => $this->developer?->id,
                'name' => $this->developer?->name,
                'logo' => $this->developer?->logo,
                'website' => $this->developer?->website,
            ],

            'publisher' => [
                'id' => $this->publisher?->id,
                'name' => $this->publisher?->name,
                'logo' => $this->publisher?->logo,
                'website' => $this->publisher?->website,
            ],

            'website' => $this->website,

            'status' => $this->status,

'platform' => $this->platforms->map(function ($platform) {

    $gameRequirement = null;

    if ($platform->pivot->game_requirement_id) {
        $gameRequirement = \App\Models\GameRequirement::with([
            'minimumRequirement',
            'recommendedRequirement',
        ])->find($platform->pivot->game_requirement_id);
    }

    return [
        'id' => $platform->id,
        'name' => $platform->name,
        'logo' => $platform->logo,

        'version' => $platform->pivot->version,

        'releaseDate' => $platform->pivot->release_date,

        'downloadSize' => $platform->pivot->download_size,

        'gameRequirement' => $gameRequirement ? [
            'id' => $gameRequirement->id,

            'minimumRequirements' => $gameRequirement->minimumRequirement,

            'recommendedRequirements' => $gameRequirement->recommendedRequirement,
        ] : null,
    ];
}),

            'genre' => $this->genres->map(function ($genre) {
                return [
                    'id' => $genre->id,
                    'name' => $genre->name,
                ];
            }),

            'images' => $this->images->map(function ($image) {
                return [
                    'id' => $image->id,
                    'path' => $image->path,
                    'type' => $image->type,
                ];
            }),

            'ratings' => $this->ratings->map(function ($rating) {
                return [
                    'id' => $rating->id,
                    'source' => $rating->source,
                    'logoSource' => $rating->logo_source,
                    'rating' => (string) $rating->rating,
                    'ratingCount' => (string) $rating->rating_count,
                ];
            }),
        ];
    }
}