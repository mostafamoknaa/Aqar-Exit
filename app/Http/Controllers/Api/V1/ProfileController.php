<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request): UserResource
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'nullable', 'email', 'unique:users,email,'.$request->user()->id],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'avatar' => ['sometimes', 'image', 'max:2048'],
        ]);
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $request->user()->update($validated);

        return new UserResource($request->user());
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $request->user()->update(['password' => Hash::make($validated['password'])]);
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Password updated. Please login again.']);
    }

    /** Buyer matching preferences: "send me opportunities that fit my cash + installment". */
    public function showPreference(Request $request): JsonResponse
    {
        return response()->json(['data' => $request->user()->buyerPreference]);
    }

    public function updatePreference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cities' => ['nullable', 'array'], 'cities.*' => ['string'],
            'unit_types' => ['nullable', 'array'], 'unit_types.*' => ['string'],
            'max_cash' => ['nullable', 'integer', 'min:0'],
            'max_installment' => ['nullable', 'integer', 'min:0'],
            'min_bedrooms' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
        $preference = $request->user()->buyerPreference()->updateOrCreate([], $validated);

        return response()->json(['data' => $preference]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $request->user()->tokens()->delete();
        $request->user()->delete();

        return response()->json(['message' => 'Account deleted.']);
    }
}
