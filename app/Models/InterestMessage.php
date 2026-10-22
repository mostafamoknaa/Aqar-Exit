<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['interest_id', 'sender_id', 'sender_role', 'body', 'read_at'])]
class InterestMessage extends Model
{
    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function interest(): BelongsTo
    {
        return $this->belongsTo(Interest::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
