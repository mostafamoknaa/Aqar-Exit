<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->savedSearches()->latest()->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'criteria' => ['required', 'array'],
            'criteria.city' => ['nullable', 'string'], 'criteria.governorate' => ['nullable', 'string'],
            'criteria.unit_type' => ['nullable', 'string'], 'criteria.category_id' => ['nullable', 'integer'],
            'criteria.max_paid' => ['nullable', 'numeric', 'min:0'], 'criteria.min_bedrooms' => ['nullable', 'integer', 'min:0'],
            'notify_on_match' => ['nullable', 'boolean'],
        ]);
        $search = $request->user()->savedSearches()->create($validated);

        return response()->json(['data' => $search], 201);
    }

    public function update(Request $request, int $savedSearch): JsonResponse
    {
        $search = $request->user()->savedSearches()->findOrFail($savedSearch);
        $search->update($request->validate([
            'name' => ['sometimes', 'string', 'max:100'], 'criteria' => ['sometimes', 'array'], 'notify_on_match' => ['nullable', 'boolean'],
        ]));

        return response()->json(['data' => $search]);
    }

    public function destroy(Request $request, int $savedSearch): JsonResponse
    {
        $request->user()->savedSearches()->whereKey($savedSearch)->delete();

        return response()->json(['message' => 'Saved search deleted.']);
    }
}
