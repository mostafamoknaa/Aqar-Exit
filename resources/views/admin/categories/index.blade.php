@extends('layouts.admin')
@section('title', __('admin.categories'))
@section('content')
<div class="flex justify-end"><a class="btn-primary" href="{{ route('admin.categories.create') }}"><x-icon name="plus" class="h-4 w-4" /> New Category</a></div>
<div class="card overflow-x-auto"><table class="w-full min-w-[560px]"><thead><tr><th class="th">{{ __('admin.category') }}</th><th class="th">Icon</th><th class="th">Listings</th><th class="th">Active</th><th class="th"></th></tr></thead><tbody>
@forelse($categories as $c)<tr class="border-b border-gray-50"><td class="td"><p class="font-semibold">{{ $c->name }}</p><p class="text-xs">{{ $c->name_ar }} · {{ $c->slug }}</p></td><td class="td"><span class="inline-flex items-center gap-2"><x-icon name="{{ $c->icon ?: 'tag' }}" /> {{ $c->icon }}</span></td><td class="td">{{ $c->listings_count }}</td><td class="td">{{ $c->is_active ? 'Yes' : 'No' }}</td>
<td class="td text-end"><div class="flex justify-end gap-2"><a class="btn-secondary" href="{{ route('admin.categories.edit', $c) }}">{{ __('admin.edit') }}</a><form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete category?')">@csrf @method('DELETE')<button class="btn-danger">{{ __('admin.delete') }}</button></form></div></td></tr>
@empty <tr><td class="td" colspan="5">No categories.</td></tr> @endforelse
</tbody></table></div>{{ $categories->links() }}
@endsection
