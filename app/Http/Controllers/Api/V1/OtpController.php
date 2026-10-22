<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function send(Request $request, OtpService $otp): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'purpose' => ['nullable', 'in:login,verify_phone,password_reset'],
        ]);
        $otp->send($validated['phone'], $validated['purpose'] ?? 'login');

        // Generic response — never reveal whether the phone exists, never return the code.
        return response()->json(['message' => 'If the phone is valid, a code has been sent.', 'expires_in_minutes' => config('aqar.otp.expiry_minutes')]);
    }

    /** OTP login: existing verified users only. Registration stays password-based. */
    public function verifyLogin(Request $request, OtpService $otp): JsonResponse
    {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:20'],
            'code' => ['required', 'string', 'size:6'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);
        $otp->verify($validated['phone'], $validated['code'], 'login');

        $user = User::where('phone', $validated['phone'])->first();
        if (! $user || ! $user->is_active) {
            return response()->json(['message' => 'No active account for this phone.'], 422);
        }
        $user->forceFill(['phone_verified_at' => $user->phone_verified_at ?? now(), 'last_login_at' => now()])->save();
        $token = $user->createToken($validated['device_name'] ?? 'mobile')->plainTextToken;

        return response()->json(['data' => ['user' => new UserResource($user), 'token' => $token, 'token_type' => 'Bearer']]);
    }

    public function verifyPhone(Request $request, OtpService $otp): JsonResponse
    {
        $validated = $request->validate(['code' => ['required', 'string', 'size:6']]);
        $otp->verify($request->user()->phone, $validated['code'], 'verify_phone');
        $request->user()->forceFill(['phone_verified_at' => now()])->save();

        return response()->json(['data' => new UserResource($request->user())]);
    }
}
