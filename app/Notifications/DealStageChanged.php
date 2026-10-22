<?php

namespace App\Notifications;

use App\Notifications\Concerns\SendsPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DealStageChanged extends Notification implements ShouldQueue
{
    use Queueable, SendsPush;

    public function __construct(public int $dealId, public string $stage) {}

    public function toArray(object $notifiable): array
    {
        return ['type' => 'deal_stage', 'deal_id' => $this->dealId, 'stage' => $this->stage];
    }
}
