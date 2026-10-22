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
            'description' => ['nullable', 'string', 'max:5000'],
            'category_id' => ['nullable', 'exists:property_categories,id'],
            'country' => ['nullable', 'string', 'max:100'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'finishing_status' => ['nullable', 'in:core_shell,semi_finished,fully_finished,furnished'],
            'amenities' => ['nullable', 'array'], 'amenities.*' => ['string', 'max:50'],
            'asking_price' => ['nullable', 'numeric', 'min:0'],
            'outstanding_fees' => ['nullable', 'numeric', 'min:0'],
            'installment_frequency' => ['nullable', 'in:monthly,quarterly,semi_annual,annual'],
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
            'total_price' => ['required', 'numeric', 'min:1'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'lte:total_price'],
            'market_price' => ['nullable', 'numeric', 'min:0'],
            'installment_amount' => ['nullable', 'numeric', 'min:0'],
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
