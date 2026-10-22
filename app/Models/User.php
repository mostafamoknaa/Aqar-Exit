<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'avatar', 'city', 'locale', 'fcm_token', 'notify_push', 'notify_database', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function isStaff(): bool
    {
        return $this->role?->isStaff() ?? false;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /** Fine-grained check against the DB-editable permission matrix (config defaults as fallback). */
    public function hasPermission(string $permission): bool
    {
        if (! $this->role) {
            return false;
        }

        return in_array($this->role->value, Permission::rolesFor($permission), true);
    }

    /** Listings this user is selling. */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class, 'seller_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteListings()
    {
        return $this->belongsToMany(Listing::class, 'favorites')->withTimestamps();
    }

    public function interests(): HasMany
    {
        return $this->hasMany(Interest::class, 'buyer_id');
    }

    public function buyerPreference(): HasOne
    {
        return $this->hasOne(BuyerPreference::class);
    }

    public function dealsAsSeller(): HasMany
    {
        return $this->hasMany(Deal::class, 'seller_id');
    }

    public function dealsAsBuyer(): HasMany
    {
        return $this->hasMany(Deal::class, 'buyer_id');
    }

    public function managedDeals(): HasMany
    {
        return $this->hasMany(Deal::class, 'case_manager_id');
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function savedSearches(): HasMany
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
