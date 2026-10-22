<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PhoneOtp;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * One-time phone codes. Codes are 6-digit random, stored bcrypt-hashed,
 * expire, have max attempts + resend cooldown, and are single-use.
 * delivery provider is config-driven; `log` is for local/staging only.
 */
class OtpService
{
    public function send(string $phone, string $purpose = 'login'): PhoneOtp
    {
        $cooldown = (int) config('aqar.otp.resend_cooldown_seconds', 60);
        $recent = PhoneOtp::where('phone', $phone)->where('purpose', $purpose)->latest()->first();
        if ($recent && $recent->created_at->gt(now()->subSeconds($cooldown))) {
            throw ValidationException::withMessages(['phone' => ["Please wait {$cooldown} seconds before requesting a new code."]]);
        }

        $code = (string) random_int(100000, 999999);
        $otp = PhoneOtp::create([
            'phone' => $phone,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes((int) config('aqar.otp.expiry_minutes', 10)),
            'ip_address' => request()?->ip(),
        ]);

        $this->deliver($phone, $code, $purpose);

        return $otp;
    }

    public function verify(string $phone, string $code, string $purpose = 'login'): bool
    {
        $otp = PhoneOtp::where('phone', $phone)->where('purpose', $purpose)
            ->whereNull('consumed_at')->latest()->first();

        if (! $otp || ! $otp->isUsable()) {
            throw ValidationException::withMessages(['code' => ['Code expired or too many attempts. Request a new code.']]);
        }

        $otp->increment('attempts');
        if (! Hash::check($code, $otp->code_hash)) {
            throw ValidationException::withMessages(['code' => ['Invalid code.']]);
        }

        $otp->update(['consumed_at' => now()]);

        return true;
    }

    private function deliver(string $phone, string $code, string $purpose): void
    {
        match (config('aqar.otp.provider')) {
            'http' => $this->deliverViaHttp($phone, $code, $purpose),
            default => Log::channel('single')->info('OTP requested', ['phone' => substr($phone, 0, 4).'****', 'purpose' => $purpose, 'code_for_dev_only' => app()->isLocal() ? $code : '[redacted]']),
        };
    }

    private function deliverViaHttp(string $phone, string $code, string $purpose): void
    {
        $endpoint = config('aqar.otp.http_endpoint');
        if (! $endpoint) {
            Log::warning('OTP http provider selected but OTP_HTTP_ENDPOINT is not configured.');

            return;
        }
        Http::timeout(8)->withToken((string) config('aqar.otp.http_token'))
            ->post($endpoint, ['to' => $phone, 'message' => "Your verification code is {$code}", 'purpose' => $purpose]);
    }
}
