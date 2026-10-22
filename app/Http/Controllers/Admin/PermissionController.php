<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PermissionController extends Controller
{
    private const ROLES = ['admin', 'manager', 'user'];

    /** Critical permissions an admin can never lock themselves out of. */
    private const LOCKED_FOR_ADMIN = ['permissions.manage', 'settings.manage', 'audit.view'];

    public function index(Request $request): View
    {
        abort_unless($request->user()->can('permissions.manage'), 403);
        Permission::syncFromConfig();
        $matrix = [];
        foreach (Permission::orderBy('name')->get() as $permission) {
            $matrix[$permission->name] = Permission::rolesFor($permission->name);
        }

        return view('admin.permissions.index', ['matrix' => $matrix, 'roles' => self::ROLES, 'labels' => collect(config('permissions.catalog', []))->map(fn ($d) => $d['label'])]);
    }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->can('permissions.manage'), 403);
        $grants = (array) $request->input('permissions', []); // ['listings.moderate' => ['admin','manager']]

        DB::transaction(function () use ($grants) {
            Permission::syncFromConfig();
            DB::table('permission_role')->delete();
            foreach (Permission::all() as $permission) {
                $roles = $grants[$permission->name] ?? [];
                if ($permission->name && in_array($permission->name, self::LOCKED_FOR_ADMIN, true) && ! in_array('admin', $roles, true)) {
                    $roles[] = 'admin'; // never lock admins out of recovery controls
                }
                foreach (array_intersect($roles, self::ROLES) as $role) {
                    DB::table('permission_role')->insert(['permission_id' => $permission->id, 'role' => $role, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        });
        Permission::flushCache();
        $audit->log('permissions.updated', null, null, ['grants' => $grants]);

        return back()->with('success', 'Permission matrix saved.');
    }
}
