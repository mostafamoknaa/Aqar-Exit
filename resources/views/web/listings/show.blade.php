@extends('layouts.web')
@section('title', $listing->title)
@push('styles')
@if($listing->latitude && $listing->longitude)<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endif
@endpush
@section('content')
@php $egp = fn($n) => $n===null ? '—' : number_format((float)$n).' EGP'; @endphp
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">
    <a href="{{ route('web.listings.index') }}" class="text-sm font-semibold text-brand-700">← All units</a>
    <div class="mt-4 grid gap-8 lg:grid-cols-3">
        <div class="flex flex-col gap-5 lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="h-80 bg-gradient-to-br from-brand-100 to-gray-100">
                    @if($listing->images->first())<img src="{{ $listing->images->first()->url }}" class="h-full w-full object-cover" alt="">
                    @else<div class="flex h-full items-center justify-center text-brand-600"><x-icon name="building" class="h-24 w-24 opacity-40" /></div>@endif
                </div>
                @if($listing->images->count() > 1)<div class="flex gap-2 overflow-x-auto p-3">@foreach($listing->images as $img)<img src="{{ $img->url }}" class="h-20 w-28 rounded-lg object-cover" alt="">@endforeach</div>@endif
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2"><h1 class="text-2xl font-bold">{{ $listing->title }}</h1><span class="badge bg-gray-100 text-gray-700">{{ $listing->reference }}</span>
                <span class="badge {{ $listing->financial_status==='verified' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">{{ $listing->financial_status==='verified' ? 'Numbers document-verified' : 'Numbers under verification' }}</span></div>
                <p class="mt-1 flex items-center gap-1 text-ink-500"><x-icon name="map-pin" class="h-4 w-4" /> {{ $listing->project?->name }} · {{ $listing->developer?->name }} · {{ $listing->city }}{{ $listing->district ? ', '.$listing->district : '' }}</p>
                @if($listing->description)<p class="mt-4 whitespace-pre-line text-ink-700">{{ $listing->description }}</p>@endif
            </div>
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach([['Type',$listing->unit_type->label()],['Area',$listing->area_sqm.' m²'],['Bedrooms',$listing->bedrooms],['Bathrooms',$listing->bathrooms],['Floor',$listing->floor ?? '—'],['Delivery',$listing->delivery_date?->toDateString() ?? '—'],['Contract',$listing->contract_date?->toDateString() ?? '—'],['Finishing',$listing->finishing_status ?? '—']] as [$k,$v])
                <div class="card p-3"><p class="text-xs text-ink-500">{{ $k }}</p><p class="font-semibold">{{ $v }}</p></div>@endforeach
            </div>
            @if($listing->amenities)<div class="flex flex-wrap gap-2">@foreach($listing->amenities as $a)<span class="badge bg-brand-50 text-brand-700">{{ $a }}</span>@endforeach</div>@endif
            @if($listing->latitude && $listing->longitude)
                <div class="card overflow-hidden"><div id="map" class="h-72"></div></div>
            @endif
        </div>

        <aside class="flex flex-col gap-4">
            <div class="card sticky top-24 flex flex-col gap-3 p-6">
                <div class="rounded-xl bg-brand-50 p-4"><p class="text-sm text-ink-500">Cash to seller (exactly what he paid)</p><p class="text-2xl font-extrabold text-brand-700">{{ $egp($listing->paid_amount) }}</p></div>
                <div class="flex justify-between text-sm"><span class="text-ink-500">Old contract total</span><span class="font-bold">{{ $egp($listing->total_price) }}</span></div>
                <div class="flex justify-between text-sm"><span class="text-ink-500">Remaining installments</span><span class="font-bold">{{ $egp($listing->remaining_amount) }}</span></div>
                @if($listing->installment_amount)<div class="flex justify-between text-sm"><span class="text-ink-500">Installment</span><span class="font-bold">{{ $egp($listing->installment_amount) }} × {{ $listing->installments_remaining }} ({{ $listing->installment_frequency }})</span></div>@endif
                @if($listing->market_price)<div class="flex justify-between text-sm"><span class="text-ink-500">Market today (est.)</span><span class="font-bold">{{ $egp($listing->market_price) }}</span></div>
                <div class="flex justify-between border-t border-dashed pt-3 text-sm"><span class="text-ink-500">You save vs market</span><span class="font-bold text-emerald-600">{{ $egp($listing->saving_vs_market) }}</span></div>@endif
                <div class="flex justify-between text-sm"><span class="text-ink-500">Est. total acquisition*</span><span class="font-bold">{{ $egp($listing->estimatedTotalCost()) }}</span></div>
                <p class="text-xs text-ink-500">*Cash + remaining + outstanding fees + buyer commission ({{ \App\Models\Setting::buyerCommissionRate()*100 }}%, on completion only). Estimates are not a promise of legal completion — assignment is finalized at the developer.</p>
                <a href="{{ route('web.evaluate') }}" class="btn-primary w-full !py-3 text-center">I'm Interested</a>
                <p class="text-center text-xs text-ink-500">Register in the mobile app to submit interest, save favorites and track your case step by step.</p>
            </div>
        </aside>
    </div>

    @if($similar->count())
    <h2 class="mb-4 mt-12 text-xl font-bold">Similar in {{ $listing->city }}</h2>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@foreach($similar as $s)<x-listing-card :listing="$s" />@endforeach</div>
    @endif
</div>
@push('scripts')
@if($listing->latitude && $listing->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>const map=L.map('map').setView([{{ $listing->latitude }},{{ $listing->longitude }}],13);L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap'}).addTo(map);L.marker([{{ $listing->latitude }},{{ $listing->longitude }}]).addTo(map);</script>
@endif
@endpush
@endsection
