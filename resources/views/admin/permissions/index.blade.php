@extends('layouts.admin')
@section('title', 'Permissions Matrix')
@section('content')
<form method="POST" action="{{ route('admin.permissions.update') }}" class="card overflow-x-auto">
    @csrf @method('PUT')
    <div class="border-b border-gray-100 px-5 py-4"><h2 class="font-bold">Role × Permission Matrix</h2><p class="text-sm text-ink-500">Checked = role may perform the action. Server enforces this on every request — hiding UI is never the protection. Admin can never remove its own recovery permissions (permissions, settings, audit).</p></div>
    <table class="w-full min-w-[640px]"><thead><tr><th class="th">Permission</th>@foreach($roles as $role)<th class="th text-center">{{ ucfirst($role) }}</th>@endforeach</tr></thead>
    <tbody>
    @foreach($matrix as $name => $granted)
        <tr class="border-b border-gray-50 hover:bg-gray-50"><td class="td"><p class="font-semibold">{{ $labels[$name] ?? $name }}</p><p class="text-xs text-ink-500"><code>{{ $name }}</code></p></td>
        @foreach($roles as $role)<td class="td text-center"><input type="checkbox" name="permissions[{{ $name }}][]" value="{{ $role }}" @checked(in_array($role, $granted, true)) class="h-4 w-4 rounded"></td>@endforeach</tr>
    @endforeach
    </tbody></table>
    <div class="flex items-center gap-3 px-5 py-4"><button class="btn-primary">{{ __('admin.save') }}</button><p class="text-xs text-ink-500">Changes apply immediately and are audit-logged.</p></div>
</form>
@endsection
