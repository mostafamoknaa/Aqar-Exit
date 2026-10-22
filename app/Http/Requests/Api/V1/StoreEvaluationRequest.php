<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required_without:user_id', 'nullable', 'string', 'max:255'],
            'phone' => ['required_without:user_id', 'nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'developer_name' => ['nullable', 'string', 'max:255'],
            'project_name' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'total_price' => ['required', 'integer', 'min:1'],
            'paid_amount' => ['required', 'integer', 'min:0', 'lte:total_price'],
            'cancellation_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
