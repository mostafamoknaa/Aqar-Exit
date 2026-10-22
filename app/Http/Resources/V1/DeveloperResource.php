<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeveloperResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'logo' => $this->logo,
            'description' => $this->description,
            'projects_count' => $this->whenCounted('projects'),
            'listings_count' => $this->whenCounted('listings'),
        ];
    }
}
