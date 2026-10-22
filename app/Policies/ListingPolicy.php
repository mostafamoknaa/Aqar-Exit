<?php

namespace App\Policies;

use App\Enums\ListingStatus;
use App\Models\Listing;
use App\Models\User;

class ListingPolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Listing $listing): bool
    {
        return $listing->status === ListingStatus::Published
            || $user?->id === $listing->seller_id
            || ($user?->isStaff() ?? false);
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function update(User $user, Listing $listing): bool
    {
        return $user->isStaff() || ($user->id === $listing->seller_id
            && ! in_array($listing->status, [ListingStatus::Completed, ListingStatus::Reserved], true));
    }

    public function delete(User $user, Listing $listing): bool
    {
        return $user->isAdmin() || $user->id === $listing->seller_id;
    }

    /** Moderation (publish/reject/review documents) is staff-only, regardless of UI visibility. */
    public function moderate(User $user, Listing $listing): bool
    {
        return $user->isStaff();
    }
}
