<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\SavedSearch;
use App\Models\User;
use App\Notifications\ListingStatusChanged;
use App\Notifications\NewMatchingListing;
use Illuminate\Validation\ValidationException;

/**
 * Listing review workflow (steps 1-6 + rejected/hidden of the 12-step case).
 * Only staff moderate; sellers resubmit via the API (MyListingController).
 */
class ListingTransitionService
{
    /** @var array<string, list<ListingStatus>> */
    public const TRANSITIONS = [
        'draft' => [ListingStatus::PendingReview],
        'pending_review' => [ListingStatus::UnderReview, ListingStatus::ChangesRequested, ListingStatus::Rejected],
        'under_review' => [ListingStatus::Published, ListingStatus::ChangesRequested, ListingStatus::Rejected],
        'changes_requested' => [ListingStatus::DocumentsResubmitted, ListingStatus::Rejected],
        'documents_resubmitted' => [ListingStatus::UnderReview, ListingStatus::Published, ListingStatus::ChangesRequested, ListingStatus::Rejected],
        'published' => [ListingStatus::Hidden, ListingStatus::Reserved],
        'reserved' => [ListingStatus::Completed, ListingStatus::Published],
        'hidden' => [ListingStatus::Published, ListingStatus::Draft],
        'completed' => [],
        'rejected' => [ListingStatus::Draft],
    ];

    public function canTransition(Listing $listing, ListingStatus $to): bool
    {
        // Direct moderation shortcuts kept for staff: publish/reject/hide from any review state.
        if (in_array($to, [ListingStatus::Published, ListingStatus::Rejected, ListingStatus::Hidden], true)
            && in_array($listing->status, [ListingStatus::PendingReview, ListingStatus::UnderReview, ListingStatus::DocumentsResubmitted, ListingStatus::Published], true)) {
            return true;
        }

        return in_array($to, self::TRANSITIONS[$listing->status->value] ?? [], true);
    }

    public function transition(Listing $listing, ListingStatus $to, User $actor, ?string $notes = null): Listing
    {
        if (! $this->canTransition($listing, $to)) {
            throw ValidationException::withMessages(['status' => ["Cannot move listing from {$listing->status->label()} to {$to->label()}."]]);
        }
        $from = $listing->status->value;
        $listing->update([
            'status' => $to,
            'verification_notes' => $notes ?? $listing->verification_notes,
            'verified_by' => $actor->id,
            'verified_at' => $to === ListingStatus::Published ? now() : $listing->verified_at,
            'published_at' => $to === ListingStatus::Published ? ($listing->published_at ?? now()) : $listing->published_at,
        ]);
        app(AuditLogger::class)->log('listing.status_changed', $listing, ['status' => $from], ['status' => $to->value], $actor);
        $listing->seller->notify(new ListingStatusChanged($listing->id, $to->value, $notes));

        if ($to === ListingStatus::Published) {
            SavedSearch::where('notify_on_match', true)->with('user')->get()
                ->filter(fn (SavedSearch $s) => $s->matches($listing))
                ->each(function (SavedSearch $s) use ($listing) {
                    $s->user->notify(new NewMatchingListing($listing->id, $s->id));
                    $s->update(['last_notified_at' => now()]);
                });
        }

        return $listing;
    }
}
