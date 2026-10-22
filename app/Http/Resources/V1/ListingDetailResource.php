<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;

/** Detail adds contract dates + seller-safe info. Still hides unit_number / exact_address / documents. */
class ListingDetailResource extends ListingResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'contract_date' => $this->contract_date?->toDateString(),
            'next_installment_date' => $this->next_installment_date?->toDateString(),
            'verified_at' => $this->verified_at?->toISOString(),
            'is_verified' => $this->verified_at !== null,
            'views_count' => $this->views_count,
            'seller' => $this->whenLoaded('seller', fn () => [
                'id' => $this->seller->id,
                'name' => $this->is_silent ? null : $this->seller->name,
                'member_since' => $this->seller->created_at?->toDateString(),
            ]),
            'interests_count' => $this->whenCounted('interests'),
        ]);
    }
}
