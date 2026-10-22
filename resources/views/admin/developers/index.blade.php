@extends('layouts.admin')
@section('title', 'Developers')
@section('content')
<div class="flex justify-end"><a class="btn-primary" href="{{ route('admin.developers.create') }}">+ New Developer</a></div>
<div class="card overflow-x-auto"><table class="w-full min-w-[640px]"><thead><tr><th class="th">Developer</th><th class="th">Projects</th><th class="th">Listings</th><th class="th">Active</th><th class="th"></th></tr></thead><tbody>
@forelse($developers as $d)<tr class="border-b border-gray-50"><td class="td"><p class="font-semibold">{{ $d->name }}</p><p class="text-xs">{{ $d->name_ar }}</p></td><td class="td">{{ $d->projects_count }}</td><td class="td">{{ $d->listings_count }}</td><td class="td">{{ $d->is_active ? 'Yes' : 'No' }}</td>
<td class="td text-end"><div class="flex justify-end gap-2"><a class="btn-secondary" href="{{ route('admin.developers.edit', $d) }}">Edit</a><form method="POST" action="{{ route('admin.developers.destroy', $d) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger">Delete</button></form></div></td></tr>
@empty <tr><td class="td" colspan="5">No developers.</td></tr> @endforelse
</tbody></table></div>
{{ $developers->links() }}
@endsection
