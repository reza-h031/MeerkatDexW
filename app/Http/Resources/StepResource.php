<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StepResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = array(
            "id" => $this->id,
            "time" => $this->time,
            "description" => $this->description,
            "images" => $this->images,
            "videos" => $this->videos
        );
        if ($this->step) {
            $data["step"] = $this->step;
        }

        return $data;
    }
}
