<?php

namespace App\Models;

use Database\Factories\InterestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['listing_id', 'buyer_id', 'message', 'cash_available', 'status', 'admin_notes'])]
class Interest extends Model
{
    /** @use HasFactory<InterestFactory> */
    use HasFactory;

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function deal(): HasOne
    {
        return $this->hasOne(Deal::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(InterestMessage::class)->oldest();
    }
}
