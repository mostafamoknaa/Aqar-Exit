<?php

namespace App\Models;

use App\Enums\ListingStatus;
use App\Enums\UnitType;
use Database\Factories\ListingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'seller_id', 'developer_id', 'project_id', 'title', 'unit_type', 'city', 'district',
    'unit_number', 'exact_address', 'floor', 'area_sqm', 'bedrooms', 'bathrooms',
    'total_price', 'paid_amount', 'remaining_amount', 'market_price', 'installment_amount',
    'installments_remaining', 'contract_date', 'delivery_date', 'next_installment_date',
    'is_silent', 'is_featured', 'status', 'verification_notes', 'verified_by', 'verified_at', 'published_at',
])]
class Listing extends Model
{
    /** @use HasFactory<ListingFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'status' => ListingStatus::class,
            'unit_type' => UnitType::class,
            'area_sqm' => 'decimal:2',
            'contract_date' => 'date',
            'delivery_date' => 'date',
            'next_installment_date' => 'date',
            'verified_at' => 'datetime',
            'published_at' => 'datetime',
            'is_silent' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Listing $listing) {
            $listing->remaining_amount = max(0, (int) $listing->total_price - (int) $listing->paid_amount);
        });
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function developer(): BelongsTo
    {
        return $this->belongsTo(Developer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class)->orderBy('sort_order');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ListingDocument::class)->latest();
    }

    public function interests(): HasMany
    {
        return $this->hasMany(Interest::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /** Buyer saves this much vs buying at today's market price. */
    public function getSavingVsMarketAttribute(): ?int
    {
        return $this->market_price ? max(0, (int) $this->market_price - (int) $this->total_price) : null;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $w) => $w->where('title', 'like', "%{$v}%")->orWhere('city', 'like', "%{$v}%")->orWhere('district', 'like', "%{$v}%")))
            ->when($filters['city'] ?? null, fn (Builder $q, $v) => $q->where('city', $v))
            ->when($filters['district'] ?? null, fn (Builder $q, $v) => $q->where('district', $v))
            ->when($filters['unit_type'] ?? null, fn (Builder $q, $v) => $q->where('unit_type', $v))
            ->when($filters['project_id'] ?? null, fn (Builder $q, $v) => $q->where('project_id', $v))
            ->when($filters['developer_id'] ?? null, fn (Builder $q, $v) => $q->where('developer_id', $v))
            ->when($filters['min_paid'] ?? null, fn (Builder $q, $v) => $q->where('paid_amount', '>=', $v))
            ->when($filters['max_paid'] ?? null, fn (Builder $q, $v) => $q->where('paid_amount', '<=', $v))
            ->when($filters['min_total'] ?? null, fn (Builder $q, $v) => $q->where('total_price', '>=', $v))
            ->when($filters['max_total'] ?? null, fn (Builder $q, $v) => $q->where('total_price', '<=', $v))
            ->when($filters['bedrooms'] ?? null, fn (Builder $q, $v) => $q->where('bedrooms', '>=', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v));
    }
}
