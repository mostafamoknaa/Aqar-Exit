<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Best-effort FCM push. If FCM_SERVER_KEY / project is not configured this is a
 * safe no-op (in-app database notifications remain the source of truth).
 * Payloads contain only type + ids — never prices, phones or contract data.
 */
class FcmService
{
    public function sendToToken(?string $token, string $title, string $body, array $data = []): bool
    {
        $key = config('aqar.fcm.server_key');
        if (! $token || ! $key) {
            return false;
        }

        try {
            $response = Http::timeout(8)->withToken($key)->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $token,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => array_map('strval', $data),
            ]);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::warning('FCM send failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
