<?php

namespace App\Notifications\Channels;

use App\Services\FcmService;
use Illuminate\Notifications\Notification;

class FcmChannel
{
    public function __construct(private FcmService $fcm) {}

    public function send(object $notifiable, Notification $notification): void
    {
        if (! ($notifiable->notify_push ?? true) || ! method_exists($notification, 'toFcm')) {
            return;
        }
        $message = $notification->toFcm($notifiable);
        $this->fcm->sendToToken($notifiable->fcm_token ?? null, $message['title'], $message['body'], $message['data'] ?? []);
    }
}
