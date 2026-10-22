<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Resources\V1\UserResource;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        abort_unless(Setting::get('registration_enabled', true), 403, 'Registration is currently disabled.');
        $user = User::create($request->safe()->except('device_name'));
        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return response()->json([
            'data' => ['user' => new UserResource($user), 'token' => $token, 'token_type' => 'Bearer'],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $login = $request->string('login')->toString();
        $user = User::where('phone', $login)->orWhere('email', $login)->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages(['login' => ['Invalid credentials.']]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages(['login' => ['Account is disabled.']]);
        }

        $user->forceFill(['last_login_at' => now()])->save();
        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return response()->json(['data' => ['user' => new UserResource($user), 'token' => $token, 'token_type' => 'Bearer']]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
