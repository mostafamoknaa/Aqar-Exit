<?php

namespace App\Notifications\Concerns;

use App\Models\Setting;
use App\Notifications\Channels\FcmChannel;

trait SendsPush
{
    public function via(object $notifiable): array
    {
        $channels = ($notifiable->notify_database ?? true) ? ['database'] : [];
        if (($notifiable->notify_push ?? true) && ($notifiable->fcm_token ?? null)) {
            $channels[] = FcmChannel::class;
        }

        return $channels;
    }

    /** Generic push text — ids/type only, never sensitive data. */
    public function toFcm(object $notifiable): array
    {
        $data = $this->toArray($notifiable);

        return [
            'title' => Setting::siteName(),
            'body' => match ($data['type'] ?? '') {
                'listing_status' => 'Your listing status changed: '.($data['status'] ?? ''),
                'new_interest' => 'New buyer interest on your listing.',
                'deal_stage' => 'Your assignment case moved to: '.($data['stage'] ?? ''),
                'matching_listing' => 'A new listing matches your saved search.',
                default => 'You have a new update.',
            },
            'data' => $data,
        ];
    }
}
