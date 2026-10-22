<?php

namespace App\Http\Resources\V1;

use App\Enums\DealStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DealResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'stage' => $this->stage?->value,
            'stage_label' => $this->stage?->label(),
            'progress_percent' => $this->progress_percent,
            'pipeline' => collect(DealStage::pipeline())->map(fn (DealStage $s) => ['key' => $s->value, 'label' => $s->label()]),
            'cash_to_seller' => (float) $this->cash_to_seller,
            'total_price' => (float) $this->total_price,
            'commission_rate' => (float) $this->commission_rate,
            'commission_amount' => (float) $this->commission_amount,
            'expected_signing_date' => $this->expected_signing_date?->toDateString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'listing' => new ListingResource($this->whenLoaded('listing')),
            'seller' => $this->whenLoaded('seller', fn () => ['id' => $this->seller->id, 'name' => $this->seller->name]),
            'buyer' => $this->whenLoaded('buyer', fn () => ['id' => $this->buyer->id, 'name' => $this->buyer->name]),
            'case_manager' => $this->whenLoaded('caseManager', fn () => $this->caseManager ? ['id' => $this->caseManager->id, 'name' => $this->caseManager->name, 'phone' => $this->caseManager->phone] : null),
            'events' => $this->whenLoaded('events', fn () => $this->events->map(fn ($e) => [
                'from_stage' => $e->from_stage, 'to_stage' => $e->to_stage, 'reason' => $e->reason,
                'actor' => $e->actor?->name, 'created_at' => $e->created_at?->toISOString(),
            ])),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
