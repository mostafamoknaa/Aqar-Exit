<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'city' => $this->city,
            'district' => $this->district,
            'image' => $this->image,
            'description' => $this->description,
            'developer' => new DeveloperResource($this->whenLoaded('developer')),
            'listings_count' => $this->whenCounted('listings'),
        ];
    }
}
