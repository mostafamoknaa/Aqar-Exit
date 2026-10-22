<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'cities', 'unit_types', 'max_cash', 'max_installment', 'min_bedrooms', 'notes'])]
class BuyerPreference extends Model
{
    protected function casts(): array
    {
        return ['cities' => 'array', 'unit_types' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
