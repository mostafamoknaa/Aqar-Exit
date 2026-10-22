<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

#[Fillable(['key', 'value', 'type', 'group'])]
class Setting extends Model
{
    public static function get(string $key, mixed $default = null): mixed
    {
        // Cache only plain scalars/arrays — never the Eloquent model itself.
        // A serialized model in the cache can be unserialized before the class
        // is loaded, producing an "incomplete object" ErrorException.
        $setting = Cache::remember("setting.v2.{$key}", 3600, function () use ($key) {
            $row = static::where('key', $key)->first();

            return $row ? ['value' => $row->value, 'type' => $row->type] : null;
        });
        if (! $setting) {
            return $default;
        }

        return match ($setting['type']) {
            'integer' => (int) $setting['value'],
            'float' => (float) $setting['value'],
            'boolean' => filter_var($setting['value'], FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode((string) $setting['value'], true),
            default => $setting['value'],
        };
    }

    public static function put(string $key, mixed $value, string $type = 'string', string $group = 'general'): void
    {
        $stored = $type === 'json' ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);
        static::updateOrCreate(['key' => $key], ['value' => $stored, 'type' => $type, 'group' => $group]);
        Cache::forget("setting.v2.{$key}");
        Cache::forget("setting.{$key}"); // legacy key that may hold a serialized model
    }

    public static function siteName(): string
    {
        return (string) static::get('site_name', config('app.name', 'Aqar Exit'));
    }

    public static function logoUrl(): ?string
    {
        $logo = static::get('site_logo');

        return $logo ? Storage::disk('public')->url($logo) : null;
    }

    public static function buyerCommissionRate(): float
    {
        return (float) static::get('buyer_commission_rate', config('aqar.buyer_commission_rate'));
    }
}
