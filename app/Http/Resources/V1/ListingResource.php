<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Public card/list item. Private fields (unit_number, exact_address, seller phone) are never exposed. */
class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'unit_type' => $this->unit_type?->value,
            'unit_type_label' => $this->unit_type?->label(),
            'city' => $this->city,
            'district' => $this->district,
            'area_sqm' => $this->area_sqm,
            'bedrooms' => $this->bedrooms,
            'bathrooms' => $this->bathrooms,
            'floor' => $this->floor,
            // Money block the mobile app renders as "cash to seller / total / remaining"
            'total_price' => (int) $this->total_price,
            'paid_amount' => (int) $this->paid_amount,
            'cash_required' => (int) $this->paid_amount,
            'remaining_amount' => (int) $this->remaining_amount,
            'market_price' => $this->market_price ? (int) $this->market_price : null,
            'saving_vs_market' => $this->saving_vs_market,
            'installment_amount' => $this->installment_amount ? (int) $this->installment_amount : null,
            'installments_remaining' => $this->installments_remaining,
            'delivery_date' => $this->delivery_date?->toDateString(),
            'is_featured' => $this->is_featured,
            'is_silent' => $this->is_silent,
            'status' => $this->status?->value,
            'cover_image' => $this->whenLoaded('images', fn () => $this->images->firstWhere('is_cover', true)?->url ?? $this->images->first()?->url),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($i) => $i->url)->values()),
            'developer' => new DeveloperResource($this->whenLoaded('developer')),
            'project' => new ProjectResource($this->whenLoaded('project')),
            'is_favorite' => $this->when(isset($this->is_favorite), fn () => (bool) $this->is_favorite),
            'published_at' => $this->published_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
