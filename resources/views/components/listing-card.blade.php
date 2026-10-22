@props(['listing'])
<a href="{{ route('web.listings.show', $listing) }}" class="card group overflow-hidden transition hover:shadow-lg">
    <div class="relative h-48 bg-gradient-to-br from-brand-100 to-gray-100">
        @if($listing->images->first())<img src="{{ $listing->images->first()->url }}" class="h-full w-full object-cover" alt="{{ $listing->title }}">
        @else<div class="flex h-full items-center justify-center text-brand-600"><x-icon name="building" class="h-16 w-16 opacity-40" /></div>@endif
        @if($listing->is_featured)<span class="badge absolute start-3 top-3 bg-brand-600 text-white">Featured</span>@endif
        <span class="badge absolute end-3 top-3 bg-white/90 text-ink-700">{{ $listing->reference }}</span>
    </div>
    <div class="flex flex-col gap-2 p-4">
        <p class="line-clamp-1 font-bold group-hover:text-brand-700">{{ $listing->title }}</p>
        <p class="flex items-center gap-1 text-sm text-ink-500"><x-icon name="map-pin" class="h-4 w-4" /> {{ $listing->project?->name ? $listing->project->name.' · ' : '' }}{{ $listing->city }}{{ $listing->district ? ' · '.$listing->district : '' }}</p>
        <p class="text-sm text-ink-500">{{ $listing->area_sqm }} m² · {{ $listing->bedrooms }} bed · {{ $listing->bathrooms }} bath</p>
        <div class="mt-1 grid grid-cols-2 gap-2 text-sm">
            <div class="rounded-lg bg-brand-50 p-2"><p class="text-xs text-ink-500">Cash to seller</p><p class="font-bold text-brand-700">{{ number_format((float)$listing->paid_amount) }} EGP</p></div>
            <div class="rounded-lg bg-gray-50 p-2"><p class="text-xs text-ink-500">Old contract price</p><p class="font-bold">{{ number_format((float)$listing->total_price) }} EGP</p></div>
        </div>
        @if($listing->saving_vs_market)<p class="text-xs font-semibold text-emerald-600">Save ~{{ number_format((float)$listing->saving_vs_market) }} EGP vs market today</p>@endif
    </div>
</a>
