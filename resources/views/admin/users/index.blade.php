@extends('layouts.admin')
@section('title', 'Users')
@section('content')
<form method="GET" class="card flex flex-wrap items-end gap-3 p-4">
    <div class="min-w-48 flex-1"><label class="label">Search</label><input name="search" class="input" value="{{ request('search') }}" placeholder="Name, phone, email…"></div>
    <div><label class="label">Role</label><select name="role" class="input"><option value="">All</option>@foreach(\App\Enums\UserRole::cases() as $r)<option value="{{ $r->value }}" @selected(request('role')===$r->value)>{{ $r->label() }}</option>@endforeach</select></div>
    <button class="btn-primary">Filter</button>
</form>
<div class="card overflow-x-auto"><table class="w-full min-w-[720px]"><thead><tr><th class="th">User</th><th class="th">Role</th><th class="th">Listings</th><th class="th">Interests</th><th class="th">Status</th><th class="th"></th></tr></thead><tbody>
@forelse($users as $user)
<tr class="border-b border-gray-50 hover:bg-gray-50"><td class="td"><p class="font-semibold">{{ $user->name }}</p><p class="text-xs text-ink-500">{{ $user->phone }} · {{ $user->email }}</p></td>
<td class="td">{{ $user->role->label() }}</td><td class="td">{{ $user->listings_count }}</td><td class="td">{{ $user->interests_count }}</td>
<td class="td"><span class="badge {{ $user->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">{{ $user->is_active ? 'Active' : 'Disabled' }}</span></td>
<td class="td text-end"><a class="btn-secondary" href="{{ route('admin.users.show', $user) }}">View</a></td></tr>
@empty <tr><td class="td" colspan="6">No users.</td></tr> @endforelse
</tbody></table></div>
{{ $users->links() }}
@endsection
