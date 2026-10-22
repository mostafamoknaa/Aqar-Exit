<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'developer_name' => $this->developer_name,
            'project_name' => $this->project_name,
            'city' => $this->city,
            'total_price' => (float) $this->total_price,
            'paid_amount' => (float) $this->paid_amount,
            'cancellation_percent' => (float) $this->cancellation_percent,
            'status' => $this->status,
            'result' => $this->result(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
