<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /** @param array<string, mixed>|null $old @param array<string, mixed>|null $new */
    public function log(string $action, ?Model $auditable = null, ?array $old = null, ?array $new = null, ?User $actor = null): void
    {
        AuditLog::create([
            'user_id' => ($actor ?? auth()->user())?->id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
        ]);
    }
}
