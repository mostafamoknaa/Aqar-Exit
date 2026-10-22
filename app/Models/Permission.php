<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

#[Fillable(['name', 'label', 'group'])]
class Permission extends Model
{
    /** Roles granted this permission: DB matrix first, config defaults as fallback. */
    public static function rolesFor(string $name): array
    {
        return Cache::remember("permission.{$name}", 3600, function () use ($name) {
            $permission = static::where('name', $name)->first();
            if ($permission) {
                $roles = DB::table('permission_role')->where('permission_id', $permission->id)->pluck('role')->all();
                if ($roles) {
                    return $roles;
                }
            }

            // Note: permission names contain dots, so never use config() dot-traversal for them.
            $catalog = config('permissions.catalog', []);

            return $catalog[$name]['roles'] ?? [];
        });
    }

    public static function flushCache(?string $name = null): void
    {
        if ($name) {
            Cache::forget("permission.{$name}");
        } else {
            foreach (array_keys(config('permissions.catalog', [])) as $key) {
                Cache::forget("permission.{$key}");
            }
        }
    }

    /** Seed the DB matrix from config defaults (idempotent). */
    public static function syncFromConfig(): void
    {
        foreach (config('permissions.catalog', []) as $name => $definition) {
            $permission = static::updateOrCreate(['name' => $name], ['label' => $definition['label'], 'group' => 'catalog']);
            foreach ($definition['roles'] as $role) {
                DB::table('permission_role')->updateOrInsert(['permission_id' => $permission->id, 'role' => $role]);
            }
        }
        static::flushCache();
    }
}
