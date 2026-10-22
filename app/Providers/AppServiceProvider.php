<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // One Gate per catalog permission, backed by the editable matrix.
        foreach (array_keys(config('permissions.catalog', [])) as $permission) {
            Gate::define($permission, fn (User $user) => $user->hasPermission($permission));
        }
    }
}
