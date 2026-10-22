<?php

namespace App\Policies;

use App\Models\Deal;
use App\Models\User;

class DealPolicy
{
    public function view(User $user, Deal $deal): bool
    {
        return $user->isStaff()
            || in_array($user->id, [$deal->buyer_id, $deal->seller_id], true)
            || $deal->case_manager_id === $user->id;
    }

    /** Only staff move cases; buyers/sellers only observe. Managers only their assigned cases unless admin. */
    public function transition(User $user, Deal $deal): bool
    {
        return $user->isAdmin() || ($user->isStaff() && $deal->case_manager_id === $user->id) || $user->isAdmin();
    }

    public function update(User $user, Deal $deal): bool
    {
        return $this->transition($user, $deal);
    }
}
