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
            'release_date' => $this->release_date,

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

'platforms' => $this->platforms->map(function ($platform) {

    $requirement = null;

    if ($platform->pivot->game_requirement_id) {
        $requirement = \App\Models\GameRequirement::with([
            'minimumRequirement',
            'recommendedRequirement',
        ])->find($platform->pivot->game_requirement_id);
    }

    return [
        'id' => $platform->id,
        'name' => $platform->name,
        'logo' => $platform->logo,

        'version' => $platform->pivot->version,
        'release_date' => $platform->pivot->release_date,
        'download_size' => $platform->pivot->download_size,

        'requirement' => $requirement ? [
            'id' => $requirement->id,

            'minimum' => $requirement->minimumRequirement,

            'recommended' => $requirement->recommendedRequirement,
        ] : null,
    ];
}),

'genres' => $this->genres->map(function ($genre) {
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
        'logo_source' => $rating->logo_source,
        'rating' => $rating->rating,
        'rating_count' => $rating->rating_count,
    ];
}),
        ];
    }
}