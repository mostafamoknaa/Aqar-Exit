<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    /** Uses Laravel password broker — reset links/tokens are never logged or returned in the response. */
    public function forgot(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        Password::sendResetLink($request->only('email'));

        // Always generic response to avoid account enumeration.
        return response()->json(['message' => 'If the email exists, a reset link has been sent.']);
    }

    public function reset(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required', 'string'], 'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);
        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->forceFill(['password' => bcrypt($password)])->save();
            $user->tokens()->delete();
        });

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset. Please login.'])
            : response()->json(['message' => 'Invalid or expired token.'], 422);
    }
}
