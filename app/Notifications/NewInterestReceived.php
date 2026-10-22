<?php

namespace App\Notifications;

use App\Notifications\Concerns\SendsPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewInterestReceived extends Notification implements ShouldQueue
{
    use Queueable, SendsPush;

    public function __construct(public int $listingId, public int $interestId) {}

    public function toArray(object $notifiable): array
    {
        // No buyer phone / financial data in push-style payloads.
        return ['type' => 'new_interest', 'listing_id' => $this->listingId, 'interest_id' => $this->interestId];
    }
}
