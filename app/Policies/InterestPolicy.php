<?php

namespace App\Policies;

use App\Models\Interest;
use App\Models\User;

class InterestPolicy
{
    public function view(User $user, Interest $interest): bool
    {
        return $user->isStaff() || $interest->buyer_id === $user->id || $interest->listing->seller_id === $user->id;
    }

    public function update(User $user, Interest $interest): bool
    {
        return $user->isStaff() || $interest->buyer_id === $user->id;
    }

    public function delete(User $user, Interest $interest): bool
    {
        return $interest->buyer_id === $user->id;
    }
}
