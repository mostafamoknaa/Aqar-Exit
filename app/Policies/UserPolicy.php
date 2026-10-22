<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, User $model): bool
    {
        return $user->isStaff() || $user->id === $model->id;
    }

    public function update(User $user, User $model): bool
    {
        return $user->isAdmin() || $user->id === $model->id;
    }

    public function manageRoles(User $user): bool
    {
        return $user->isAdmin();
    }
}
