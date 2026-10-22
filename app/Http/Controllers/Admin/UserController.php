<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::withCount(['listings', 'interests', 'dealsAsBuyer'])
            ->when($request->input('search'), fn ($q, $v) => $q->where(fn ($w) => $w->where('name', 'like', "%{$v}%")->orWhere('phone', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")))
            ->when($request->input('role'), fn ($q, $v) => $q->where('role', $v))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->load(['listings.project', 'interests.listing', 'dealsAsBuyer.listing', 'dealsAsSeller.listing', 'buyerPreference', 'evaluations']);

        return view('admin.users.show', compact('user'));
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_unless(auth()->user()->can('users.manage'), 403);
        abort_if($user->id === auth()->id(), 422, 'You cannot disable yourself.');
        $user->update(['is_active' => ! $user->is_active]);
        $user->tokens()->delete();

        return back()->with('success', 'User status updated.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->can('users.manage'), 403);
        $request->validate(['role' => ['required', 'in:admin,manager,user']]);
        $user->update(['role' => $request->input('role')]);

        return back()->with('success', 'Role updated.');
    }
}
