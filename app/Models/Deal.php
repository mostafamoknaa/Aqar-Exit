<?php

namespace App\Models;

use App\Enums\DealStage;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'listing_id', 'seller_id', 'buyer_id', 'case_manager_id', 'interest_id', 'stage',
    'cash_to_seller', 'total_price', 'commission_rate', 'commission_amount', 'notes',
    'expected_signing_date', 'completed_at', 'cancelled_at',
])]
class Deal extends Model
{
    /** @use HasFactory<DealFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'stage' => DealStage::class,
            'commission_rate' => 'decimal:4',
            'expected_signing_date' => 'date',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Deal $deal) {
            $deal->commission_amount = (int) round((int) $deal->total_price * (float) $deal->commission_rate);
            if ($deal->stage === DealStage::Completed && ! $deal->completed_at) {
                $deal->completed_at = now();
            }
            if ($deal->stage === DealStage::Cancelled && ! $deal->cancelled_at) {
                $deal->cancelled_at = now();
            }
        });
    }

    /** 0-100 progress for the mobile deal tracker (cancelled = 0). */
    public function getProgressPercentAttribute(): int
    {
        $pipeline = DealStage::pipeline();
        $index = array_search($this->stage, $pipeline, true);

        return $index === false ? 0 : (int) round($index / (count($pipeline) - 1) * 100);
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function caseManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'case_manager_id');
    }

    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class);
    }
}
