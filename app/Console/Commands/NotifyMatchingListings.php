<?php

namespace App\Console\Commands;

use App\Models\Listing;
use App\Models\SavedSearch;
use App\Notifications\NewMatchingListing;
use Illuminate\Console\Command;

class NotifyMatchingListings extends Command
{
    protected $signature = 'aqar:notify-matching {--hours=24 : Only listings published within this many hours}';

    protected $description = 'Notify users whose saved searches match recently published listings';

    public function handle(): int
    {
        $listings = Listing::published()->where('published_at', '>=', now()->subHours((int) $this->option('hours')))->get();
        $sent = 0;
        foreach (SavedSearch::where('notify_on_match', true)->with('user')->get() as $search) {
            foreach ($listings as $listing) {
                // Skip listings the owner published themselves and already-notified searches for this listing.
                if ($listing->seller_id === $search->user_id) {
                    continue;
                }
                if ($search->matches($listing)) {
                    $search->user->notify(new NewMatchingListing($listing->id, $search->id));
                    $search->update(['last_notified_at' => now()]);
                    $sent++;
                }
            }
        }
        $this->info("Sent {$sent} matching notifications for {$listings->count()} recent listings.");

        return self::SUCCESS;
    }
}
