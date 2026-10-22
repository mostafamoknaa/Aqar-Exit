@extends('layouts.admin')
@section('title', 'Listings')
@section('content')
<form method="GET" class="card flex flex-wrap items-end gap-3 p-4">
    <div class="min-w-48 flex-1"><label class="label">Search</label><input name="search" class="input" value="{{ request('search') }}" placeholder="Title, city…"></div>
    <div><label class="label">Status</label><select name="status" class="input"><option value="">All</option>@foreach (\App\Enums\ListingStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div><label class="label">City</label><input name="city" class="input" value="{{ request('city') }}"></div>
    <button class="btn-primary">Filter</button>
</form>

<div class="card overflow-x-auto">
<table class="w-full min-w-[760px]"><thead><tr class="border-b border-gray-100"><th class="th">Listing</th><th class="th">Seller</th><th class="th">Cash / Total</th><th class="th">Status</th><th class="th">Docs</th><th class="th"></th></tr></thead>
<tbody>
@forelse ($listings as $listing)
<tr class="border-b border-gray-50 hover:bg-gray-50">
    <td class="td"><p class="font-semibold">{{ $listing->title }} @if($listing->is_featured)⭐@endif @if($listing->is_silent)<span class="badge bg-gray-100 text-gray-600">Silent</span>@endif</p><p class="text-xs text-ink-500">{{ $listing->project?->name }} · {{ $listing->city }} · {{ $listing->area_sqm }} m²</p></td>
    <td class="td">{{ $listing->seller->name }}<p class="text-xs text-ink-500">{{ $listing->seller->phone }}</p></td>
    <td class="td"><span class="font-semibold">{{ number_format($listing->paid_amount) }}</span> / {{ number_format($listing->total_price) }} EGP</td>
    <td class="td"><span class="badge {{ $listing->status->badgeColor() }}">{{ $listing->status->label() }}</span></td>
    <td class="td">{{ $listing->documents_count ?? $listing->documents()->count() }}</td>
    <td class="td text-end"><a class="btn-secondary" href="{{ route('admin.listings.show', $listing) }}">Review</a></td>
</tr>
@empty <tr><td class="td" colspan="6">No listings found.</td></tr> @endforelse
</tbody></table></div>
{{ $listings->links() }}
@endsection
