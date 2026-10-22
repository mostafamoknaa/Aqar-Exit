@extends('layouts.admin')
@section('title', __('admin.locations'))
@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
<form method="GET" class="flex items-end gap-2"><select name="type" class="input"><option value="">All types</option>@foreach(['country','governorate','city','district'] as $t)<option @selected(request('type')===$t)>{{ $t }}</option>@endforeach</select><button class="btn-primary">{{ __('admin.filter') }}</button></form>
<a class="btn-primary" href="{{ route('admin.locations.create') }}"><x-icon name="plus" class="h-4 w-4" /> New Location</a></div>
<div class="card overflow-x-auto"><table class="w-full min-w-[640px]"><thead><tr><th class="th">Location</th><th class="th">{{ __('admin.type') }}</th><th class="th">{{ __('admin.parent') }}</th><th class="th">{{ __('admin.children') }}</th><th class="th"></th></tr></thead><tbody>
@forelse($locations as $l)<tr class="border-b border-gray-50"><td class="td"><p class="font-semibold">{{ $l->name }}</p><p class="text-xs">{{ $l->name_ar }}</p></td><td class="td"><span class="badge bg-sky-100 text-sky-800">{{ $l->type }}</span></td><td class="td">{{ $l->parent?->name ?? '—' }}</td><td class="td">{{ $l->children_count }}</td>
<td class="td text-end"><div class="flex justify-end gap-2"><a class="btn-secondary" href="{{ route('admin.locations.edit', $l) }}">{{ __('admin.edit') }}</a><form method="POST" action="{{ route('admin.locations.destroy', $l) }}" onsubmit="return confirm('Delete location and its children?')">@csrf @method('DELETE')<button class="btn-danger">{{ __('admin.delete') }}</button></form></div></td></tr>
@empty <tr><td class="td" colspan="5">No locations.</td></tr> @endforelse
</tbody></table></div>{{ $locations->links() }}
@endsection
