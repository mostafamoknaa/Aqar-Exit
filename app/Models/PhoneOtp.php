<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['phone', 'purpose', 'code_hash', 'attempts', 'expires_at', 'consumed_at', 'ip_address'])]
class PhoneOtp extends Model
{
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'consumed_at' => 'datetime'];
    }

    public function isUsable(): bool
    {
        return ! $this->consumed_at && $this->expires_at->isFuture() && $this->attempts < (int) config('aqar.otp.max_attempts', 5);
    }
}
