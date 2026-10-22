<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'name', 'criteria', 'notify_on_match', 'last_notified_at'])]
class SavedSearch extends Model
{
    protected function casts(): array
    {
        return ['criteria' => 'array', 'notify_on_match' => 'boolean', 'last_notified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Explicit matching rules — a match never promises suitability or completion. */
    public function matches(Listing $listing): bool
    {
        $c = $this->criteria;

        return (! isset($c['city']) || $listing->city === $c['city'])
            && (! isset($c['governorate']) || $listing->governorate === $c['governorate'])
            && (! isset($c['unit_type']) || $listing->unit_type?->value === $c['unit_type'])
            && (! isset($c['category_id']) || (int) $listing->category_id === (int) $c['category_id'])
            && (! isset($c['max_paid']) || (float) $listing->paid_amount <= (float) $c['max_paid'])
            && (! isset($c['min_bedrooms']) || $listing->bedrooms >= (int) $c['min_bedrooms']);
    }
}
