@extends('layouts.admin')
@section('title', 'Buyer Interests')
@section('content')
<form method="GET" class="card flex flex-wrap items-end gap-3 p-4"><div><label class="label">{{ __('admin.status') }}</label><select name="status" class="input"><option value="">All</option>@foreach(['new','contacted','qualified','converted','rejected'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select></div><button class="btn-primary">{{ __('admin.filter') }}</button></form>
<div class="card overflow-x-auto"><table class="w-full min-w-[720px]"><thead><tr><th class="th">{{ __('admin.buyer') }}</th><th class="th">{{ __('admin.listing') }}</th><th class="th">{{ __('admin.cash_available') }}</th><th class="th">{{ __('admin.cash_required') }}</th><th class="th">{{ __('admin.status') }}</th><th class="th">{{ __('admin.update') }}</th></tr></thead><tbody>
@forelse($interests as $interest)
<tr class="border-b border-gray-50"><td class="td"><p class="font-semibold">{{ $interest->buyer->name }}</p><p class="text-xs">{{ $interest->buyer->phone }}</p></td>
<td class="td"><a class="text-brand-700 underline" href="{{ route('admin.listings.show', $interest->listing) }}">{{ $interest->listing->title }}</a><p class="text-xs">{{ $interest->message }}</p></td>
<td class="td">{{ $interest->cash_available ? number_format($interest->cash_available) : '—' }}</td>
<td class="td font-semibold">{{ number_format($interest->listing->paid_amount) }}</td><td class="td">{{ $interest->status }}</td>
<td class="td"><form method="POST" action="{{ route('admin.interests.update', $interest) }}" class="flex gap-2">@csrf @method('PATCH')<select name="status" class="input !w-auto">@foreach(['new','contacted','qualified','converted','rejected'] as $s)<option @selected($interest->status===$s)>{{ $s }}</option>@endforeach</select><button class="btn-primary">{{ __('admin.save') }}</button></form></td></tr>
@empty <tr><td class="td" colspan="6">No interests.</td></tr> @endforelse
</tbody></table></div>
{{ $interests->links() }}
@endsection
