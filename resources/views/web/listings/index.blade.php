@extends('layouts.web')
@section('title', 'Browse Units')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <h1 class="mb-6 text-2xl font-bold">Browse Opportunities <span class="text-base font-normal text-ink-500">({{ $listings->total() }} units · no overprice)</span></h1>
    <form method="GET" class="card mb-8 flex flex-wrap items-end gap-3 p-4">
        <div class="min-w-44 flex-1"><label class="label">Search</label><input name="search" class="input" value="{{ request('search') }}" placeholder="Project, city…"></div>
        <div><label class="label">Category</label><select name="category_id" class="input"><option value="">All</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->localizedName() }}</option>@endforeach</select></div>
        <div><label class="label">Governorate</label><select name="governorate" class="input"><option value="">All</option>@foreach($governorates as $g)<option @selected(request('governorate')===$g)>{{ $g }}</option>@endforeach</select></div>
        <div><label class="label">City</label><select name="city" class="input"><option value="">All</option>@foreach($cities as $c)<option @selected(request('city')===$c)>{{ $c }}</option>@endforeach</select></div>
        <div><label class="label">Max cash to seller</label><input name="max_paid" type="number" class="input" value="{{ request('max_paid') }}" placeholder="2,000,000"></div>
        <div><label class="label">Bedrooms +</label><input name="bedrooms" type="number" class="input" value="{{ request('bedrooms') }}"></div>
        <div><label class="label">Sort</label><select name="sort" class="input"><option value="">Newest</option><option value="cash_low" @selected(request('sort')==='cash_low')>Lowest cash</option><option value="total_low" @selected(request('sort')==='total_low')>Lowest total</option></select></div>
        <button class="btn-primary"><x-icon name="search" class="h-4 w-4" /> Filter</button>
        <a href="{{ route('web.listings.index') }}" class="btn-secondary">Reset</a>
    </form>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($listings as $listing)<x-listing-card :listing="$listing" />@empty
            <div class="card col-span-full flex flex-col items-center gap-2 p-12 text-center"><x-icon name="search" class="h-10 w-10 text-gray-300" /><p class="font-bold">No units match your filters</p><p class="text-sm text-ink-500">Try widening the cash range, or register your budget in the mobile app and we'll notify you.</p></div>
        @endforelse
    </div>
    <div class="mt-8">{{ $listings->links() }}</div>
</div>
@endsection
