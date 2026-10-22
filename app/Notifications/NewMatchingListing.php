<?php

namespace App\Notifications;

use App\Notifications\Concerns\SendsPush;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewMatchingListing extends Notification implements ShouldQueue
{
    use Queueable, SendsPush;

    public function __construct(public int $listingId, public int $savedSearchId) {}

    public function toArray(object $notifiable): array
    {
        return ['type' => 'matching_listing', 'listing_id' => $this->listingId, 'saved_search_id' => $this->savedSearchId];
    }
}
