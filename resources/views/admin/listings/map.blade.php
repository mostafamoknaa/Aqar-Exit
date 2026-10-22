@extends('layouts.admin')
@section('title', 'Listings Map')
@push('styles')@endpush
@section('content')
<div class="card overflow-hidden">
    <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4"><h2 class="font-bold">{{ $listings->count() }} geocoded listings</h2><a class="btn-secondary" href="{{ route('admin.listings.index') }}">{{ __('admin.table_view') }}</a></div>
    <div id="admin-map" class="h-[560px]"></div>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const listings = @json($listings->map(fn($l) => ['id'=>$l->id,'title'=>$l->title,'lat'=>(float)$l->latitude,'lng'=>(float)$l->longitude,'status'=>$l->status->value,'cash'=>(float)$l->paid_amount,'url'=>route('admin.listings.show',$l)]));
const map = L.map('admin-map').setView(listings.length ? [listings[0].lat, listings[0].lng] : [30.0444, 31.2357], listings.length ? 10 : 6);
L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {attribution: '© OpenStreetMap'}).addTo(map);
listings.forEach(l => L.marker([l.lat, l.lng]).addTo(map).bindPopup(`<b>${l.title}</b><br>${l.status} · ${l.cash.toLocaleString()} EGP cash<br><a href="${l.url}">Review →</a>`));
</script>
@endsection
