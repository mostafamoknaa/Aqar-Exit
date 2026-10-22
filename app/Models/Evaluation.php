<?php

namespace App\Models;

use App\Services\ExitCalculatorService;
use Database\Factories\EvaluationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'name', 'phone', 'email', 'developer_name', 'project_name', 'city',
    'total_price', 'paid_amount', 'cancellation_percent', 'status', 'admin_notes',
])]
class Evaluation extends Model
{
    /** @use HasFactory<EvaluationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['cancellation_percent' => 'decimal:2'];
    }

    /** @return array<string, int|float> */
    public function result(): array
    {
        return app(ExitCalculatorService::class)->calculate(
            (int) $this->total_price,
            (int) $this->paid_amount,
            (float) $this->cancellation_percent,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
