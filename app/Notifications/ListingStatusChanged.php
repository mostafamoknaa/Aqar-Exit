<?php

namespace App\Notifications;

use App\Notifications\Concerns\SendsPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ListingStatusChanged extends Notification implements ShouldQueue
{
    use Queueable, SendsPush;

    public function __construct(public int $listingId, public string $status, public ?string $notes = null) {}

    public function toArray(object $notifiable): array
    {
        return ['type' => 'listing_status', 'listing_id' => $this->listingId, 'status' => $this->status, 'notes' => $this->notes];
    }
}
