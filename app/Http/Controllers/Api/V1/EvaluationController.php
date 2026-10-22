<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEvaluationRequest;
use App\Http\Resources\V1\EvaluationResource;
use App\Models\Evaluation;
use App\Services\ExitCalculatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    /** Instant, no-save calculator (also available to guests). */
    public function calculate(Request $request, ExitCalculatorService $calculator): JsonResponse
    {
        $validated = $request->validate([
            'total_price' => ['required', 'integer', 'min:1'],
            'paid_amount' => ['required', 'integer', 'min:0', 'lte:total_price'],
            'cancellation_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return response()->json(['data' => $calculator->calculate($validated['total_price'], $validated['paid_amount'], $validated['cancellation_percent'] ?? null)]);
    }

    /** Save a lead: admin/case-manager follows up and prepares the free exit report. */
    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $evaluation = Evaluation::create([
            ...$request->safe()->except(['name', 'phone']),
            'user_id' => $user?->id,
            'name' => $request->input('name', $user?->name),
            'phone' => $request->input('phone', $user?->phone),
            'cancellation_percent' => $request->input('cancellation_percent', config('aqar.default_cancellation_percent')),
        ]);

        return response()->json(['data' => new EvaluationResource($evaluation)], 201);
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => EvaluationResource::collection($request->user()->evaluations()->latest()->paginate(15))]);
    }
}
