@extends('layouts.web')
@section('title', 'Seller recovers what he paid — buyer takes the old contract price')
@section('content')
<section class="bg-gradient-to-b from-brand-50 to-white">
    <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 lg:grid-cols-2 lg:py-20">
        <div class="flex flex-col gap-5">
            <h1 class="text-3xl font-extrabold leading-tight sm:text-5xl">The seller gets back <span class="text-brand-700">everything he paid</span>. The buyer gets the <span class="text-brand-700">old contract price</span>.</h1>
            <p class="text-lg text-ink-500">Can't continue your installments? Sell your unit with zero overprice and recover your cash instead of losing up to 15% in developer cancellation. Looking to buy? Take an old contract price and continue the installments. Every number is document-verified, and we walk with you through the official assignment step by step.</p>
            <div class="flex flex-wrap gap-3"><a href="{{ route('web.evaluate') }}" class="btn-primary !px-6 !py-3 !text-base">Evaluate My Contract — Free</a><a href="{{ route('web.listings.index') }}" class="btn-secondary !px-6 !py-3 !text-base">Browse Opportunities</a></div>
            <div class="flex gap-8 pt-2">
                <div><p class="text-2xl font-bold">{{ number_format($stats['listings']) }}</p><p class="text-sm text-ink-500">Published units</p></div>
                <div><p class="text-2xl font-bold">{{ number_format((float)$stats['market_value']/1e9, 1) }}B</p><p class="text-sm text-ink-500">EGP market value</p></div>
                <div><p class="text-2xl font-bold">0%</p><p class="text-sm text-ink-500">Seller commission</p></div>
            </div>
        </div>
        <div class="card flex flex-col gap-4 p-6">
            <h2 class="flex items-center gap-2 font-bold"><x-icon name="calculator" class="text-brand-600" /> Know your position in a minute</h2>
            <form method="POST" action="{{ route('web.evaluate.calculate') }}" class="flex flex-col gap-3">
                @csrf
                <div><label class="label">Total unit price (EGP)</label><input name="total_price" type="number" min="1" class="input" placeholder="10,000,000" required></div>
                <div><label class="label">Amount you already paid (EGP)</label><input name="paid_amount" type="number" min="0" class="input" placeholder="2,000,000" required></div>
                <button class="btn-primary w-full">Calculate — Free</button>
                <p class="text-center text-xs text-ink-500">Example: cancel and lose 1.5M, or exit with us and recover the full 2M cash.</p>
            </form>
        </div>
    </div>
</section>

@if($categories->count())
<section class="mx-auto max-w-7xl px-4 py-10 sm:px-6"><div class="flex flex-wrap gap-3">
    @foreach($categories as $cat)<a href="{{ route('web.listings.index', ['category_id' => $cat->id]) }}" class="flex items-center gap-2 rounded-full border border-gray-200 px-4 py-2 text-sm font-semibold hover:border-brand-500 hover:text-brand-700"><x-icon name="{{ $cat->icon ?: 'tag' }}" class="h-4 w-4" /> {{ $cat->localizedName() }}</a>@endforeach
</div></section>
@endif

<section class="mx-auto max-w-7xl px-4 py-6 sm:px-6">
    <div class="mb-5 flex items-end justify-between"><h2 class="text-2xl font-bold">Featured Opportunities</h2><a href="{{ route('web.listings.index') }}" class="font-semibold text-brand-700">View all →</a></div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@forelse($featured as $listing)<x-listing-card :listing="$listing" />@empty <p class="text-ink-500">No featured units yet.</p>@endforelse</div>
</section>

<section id="how" class="bg-gray-50 py-14"><div class="mx-auto max-w-7xl px-4 sm:px-6">
    <h2 class="mb-8 text-center text-2xl font-bold">How It Works — 5 Clear Steps</h2>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
        @foreach([['calculator','Evaluate your contract','Enter your contract data and know your position for free.'],['document','Upload documents','Contract and payment receipts — we verify every number.'],['check-badge','We prepare your opportunity','Verified, trusted listing for buyers — no overprice, ever.'],['users','We find the buyer','Matched with buyers who can actually continue.'],['handshake','Assignment transfer','We walk with you to signing and cash collection.']] as [$icon,$t,$d])
            <div class="card flex flex-col gap-2 p-5"><x-icon name="{{ $icon }}" class="h-8 w-8 text-brand-600" /><p class="font-bold">{{ $t }}</p><p class="text-sm text-ink-500">{{ $d }}</p></div>
        @endforeach
    </div>
    <div class="card mx-auto mt-10 grid max-w-4xl gap-4 p-6 md:grid-cols-2">
        <div class="rounded-xl bg-red-50 p-5"><p class="font-bold text-red-700">Return it to the developer</p><p class="mt-2 text-sm">Cancellation deduction up to 15% of the <em>whole</em> unit price, the rest back over years — you can lose 75% of what you paid.</p></div>
        <div class="rounded-xl bg-emerald-50 p-5"><p class="font-bold text-emerald-700">Assign it through {{ \App\Models\Setting::siteName() }}</p><p class="mt-2 text-sm">Recover 100% of what you paid, cash on assignment. Buyer pays only {{ \App\Models\Setting::buyerCommissionRate()*100 }}% on completion. Seller pays zero.</p></div>
    </div>
</div></section>

<section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
    <div class="mb-5 flex items-end justify-between"><h2 class="text-2xl font-bold">Latest Units</h2><a href="{{ route('web.listings.index') }}" class="font-semibold text-brand-700">View all →</a></div>
    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">@forelse($latest as $listing)<x-listing-card :listing="$listing" />@empty <p class="text-ink-500">No units yet — check back soon.</p>@endforelse</div>
    @if($developers->count())<div class="mt-12 flex flex-wrap items-center gap-3"><span class="font-bold">Developers:</span>@foreach($developers as $d)<a href="{{ route('web.listings.index', ['developer_id' => $d->id]) }}" class="rounded-full bg-gray-100 px-4 py-2 text-sm font-semibold hover:bg-brand-100">{{ $d->name }} ({{ $d->listings_count }})</a>@endforeach</div>@endif
</section>
@endsection
