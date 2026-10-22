<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /** Signed link sent by resend(); link itself expires. */
    public function verify(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);
        abort_unless(hash_equals(sha1($user->email), $hash), 403, 'Invalid verification link.');
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verified.']);
    }

    public function resend(Request $request): JsonResponse
    {
        if (! $request->user()->email) {
            return response()->json(['message' => 'No email on account.'], 422);
        }
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }
        // Reuse Laravel's signed-URL verification flow; delivery depends on MAIL_* config.
        $url = URL::temporarySignedRoute('api.v1.verification.verify', now()->addMinutes(60), [
            'id' => $request->user()->id, 'hash' => sha1($request->user()->email),
        ]);
        Notification::route('mail', $request->user()->email)
            ->notify(new VerifyEmail);

        return response()->json(['message' => 'Verification email sent if mail is configured.']);
    }
}
