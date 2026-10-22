<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'message' => $this->message,
            'cash_available' => $this->cash_available ? (float) $this->cash_available : null,
            'listing' => new ListingResource($this->whenLoaded('listing')),
            'buyer' => $this->whenLoaded('buyer', fn () => ['id' => $this->buyer->id, 'name' => $this->buyer->name]),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
