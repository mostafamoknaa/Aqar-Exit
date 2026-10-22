<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\UnitType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'developer_id' => ['nullable', 'exists:developers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'unit_type' => ['required', Rule::enum(UnitType::class)],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'unit_number' => ['nullable', 'string', 'max:50'],
            'exact_address' => ['nullable', 'string'],
            'floor' => ['nullable', 'integer', 'min:0', 'max:200'],
            'area_sqm' => ['required', 'numeric', 'min:20'],
            'bedrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            'bathrooms' => ['nullable', 'integer', 'min:0', 'max:20'],
            // Aqar Exit rule enforced server-side: no "over" field exists at all.
            'total_price' => ['required', 'integer', 'min:1'],
            'paid_amount' => ['required', 'integer', 'min:0', 'lte:total_price'],
            'market_price' => ['nullable', 'integer', 'min:0'],
            'installment_amount' => ['nullable', 'integer', 'min:0'],
            'installments_remaining' => ['nullable', 'integer', 'min:0'],
            'contract_date' => ['nullable', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'next_installment_date' => ['nullable', 'date'],
            'is_silent' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
            // submit=true moves draft -> pending_review for admin verification
            'submit' => ['nullable', 'boolean'],
        ];
    }
}
