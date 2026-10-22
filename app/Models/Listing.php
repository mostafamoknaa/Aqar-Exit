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
use Illuminate\Support\Str;

#[Fillable([
    'seller_id', 'developer_id', 'project_id', 'category_id', 'reference', 'title', 'description', 'unit_type',
    'country', 'governorate', 'city', 'district', 'latitude', 'longitude', 'finishing_status', 'amenities',
    'unit_number', 'exact_address', 'floor', 'area_sqm', 'bedrooms', 'bathrooms',
    'total_price', 'paid_amount', 'remaining_amount', 'market_price', 'asking_price', 'outstanding_fees',
    'installment_amount', 'installment_frequency', 'installments_remaining', 'contract_date', 'delivery_date', 'next_installment_date',
    'is_silent', 'is_featured', 'status', 'financial_status', 'verified_total_price', 'verified_paid_amount',
    'financial_notes', 'financial_verified_by', 'financial_verified_at',
    'verification_notes', 'verified_by', 'verified_at', 'published_at',
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
            'financial_verified_at' => 'datetime',
            'published_at' => 'datetime',
            'is_silent' => 'boolean',
            'is_featured' => 'boolean',
            'amenities' => 'array',
            'total_price' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'market_price' => 'decimal:2',
            'asking_price' => 'decimal:2',
            'outstanding_fees' => 'decimal:2',
            'installment_amount' => 'decimal:2',
            'verified_total_price' => 'decimal:2',
            'verified_paid_amount' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Listing $listing) {
            $listing->reference ??= 'AQX-'.strtoupper(Str::random(8));
        });
        static::saving(function (Listing $listing) {
            // Derived value only — reported figures are never silently overwritten.
            $listing->remaining_amount = bcsub((string) $listing->total_price, (string) $listing->paid_amount, 2);
            if (bccomp($listing->remaining_amount, '0', 2) < 0) {
                $listing->remaining_amount = '0.00';
            }
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PropertyCategory::class, 'category_id');
    }

    public function financialVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financial_verified_by');
    }

    /** True when verified figures do not reconcile with owner-reported figures. */
    public function getHasFinancialDiscrepancyAttribute(): bool
    {
        return $this->financial_status === 'discrepancy';
    }

    /** Estimated total acquisition cost for buyer: cash to seller + remaining + outstanding fees + buyer commission. */
    public function estimatedTotalCost(): string
    {
        $commission = bcmul((string) $this->total_price, (string) Setting::buyerCommissionRate(), 2);

        return bcadd(bcadd((string) $this->paid_amount, (string) $this->remaining_amount, 2), bcadd((string) ($this->outstanding_fees ?? 0), $commission, 2), 2);
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
    public function getSavingVsMarketAttribute(): ?string
    {
        if (! $this->market_price) {
            return null;
        }
        $diff = bcsub((string) $this->market_price, (string) $this->total_price, 2);

        return bccomp($diff, '0', 2) > 0 ? $diff : '0.00';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ListingStatus::Published);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, $v) => $q->where(fn (Builder $w) => $w->where('title', 'like', "%{$v}%")->orWhere('city', 'like', "%{$v}%")->orWhere('district', 'like', "%{$v}%")))
            ->when($filters['governorate'] ?? null, fn (Builder $q, $v) => $q->where('governorate', $v))
            ->when($filters['category_id'] ?? null, fn (Builder $q, $v) => $q->where('category_id', $v))
            ->when($filters['min_area'] ?? null, fn (Builder $q, $v) => $q->where('area_sqm', '>=', $v))
            ->when($filters['max_area'] ?? null, fn (Builder $q, $v) => $q->where('area_sqm', '<=', $v))
            ->when($filters['max_installment'] ?? null, fn (Builder $q, $v) => $q->where('installment_amount', '<=', $v))
            ->when($filters['max_remaining'] ?? null, fn (Builder $q, $v) => $q->where('remaining_amount', '<=', $v))
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
