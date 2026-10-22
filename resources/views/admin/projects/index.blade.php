@extends('layouts.admin')
@section('title', 'Projects')
@section('content')
<div class="flex justify-end"><a class="btn-primary" href="{{ route('admin.projects.create') }}">+ New Project</a></div>
<div class="card overflow-x-auto"><table class="w-full min-w-[640px]"><thead><tr><th class="th">{{ __('admin.project') }}</th><th class="th">{{ __('admin.developer') }}</th><th class="th">{{ __('admin.city') }}</th><th class="th">Listings</th><th class="th"></th></tr></thead><tbody>
@forelse($projects as $p)<tr class="border-b border-gray-50"><td class="td"><p class="font-semibold">{{ $p->name }}</p><p class="text-xs">{{ $p->name_ar }}</p></td><td class="td">{{ $p->developer->name }}</td><td class="td">{{ $p->city }} · {{ $p->district }}</td><td class="td">{{ $p->listings_count }}</td>
<td class="td text-end"><div class="flex justify-end gap-2"><a class="btn-secondary" href="{{ route('admin.projects.edit', $p) }}">{{ __('admin.edit') }}</a><form method="POST" action="{{ route('admin.projects.destroy', $p) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger">{{ __('admin.delete') }}</button></form></div></td></tr>
@empty <tr><td class="td" colspan="5">No projects.</td></tr> @endforelse
</tbody></table></div>
{{ $projects->links() }}
@endsection
