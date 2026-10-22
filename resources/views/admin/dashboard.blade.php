@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')
@php $fmt = fn($n) => number_format($n); $egp = fn($n) => number_format($n).' EGP'; @endphp

<div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach ([
        ['Listings', $stats['listings_total'], '⏳ '.$stats['listings_pending'].' pending review', route('admin.listings.index')],
        ['Published', $stats['listings_published'], 'Market value '.$egp($stats['market_value']), route('admin.listings.index', ['status'=>'published'])],
        ['Users', $stats['users'], $stats['interests_new'].' new interests', route('admin.users.index')],
        ['Active Deals', $stats['deals_active'], $stats['deals_completed'].' completed', route('admin.deals.index')],
        ['New Evaluations', $stats['evaluations_new'], 'Free exit calculator leads', route('admin.evaluations.index', ['status'=>'new'])],
        ['Commission Earned', $egp($stats['commission_earned']), 'Buyer 1.25% on completion', route('admin.deals.index', ['stage'=>'completed'])],
    ] as [$label, $value, $hint, $url])
        <a href="{{ $url }}" class="card flex flex-col gap-1 p-5 transition hover:shadow-md">
            <span class="text-sm font-medium text-ink-500">{{ $label }}</span>
            <span class="text-2xl font-bold">{{ is_numeric($value) ? $fmt($value) : $value }}</span>
            <span class="text-xs text-ink-500">{{ $hint }}</span>
        </a>
    @endforeach
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <div class="card xl:col-span-2">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4"><h2 class="font-bold">{{ __('admin.verification_queue') }}</h2><a class="text-sm font-semibold text-brand-700" href="{{ route('admin.listings.index', ['status'=>'pending_review']) }}">View all →</a></div>
        <div class="overflow-x-auto"><table class="w-full min-w-[520px]">
            <thead><tr class="border-b border-gray-100 text-start"><th class="th">{{ __('admin.listing') }}</th><th class="th">{{ __('admin.seller') }}</th><th class="th">{{ __('admin.cash_paid') }}</th><th class="th"></th></tr></thead>
            <tbody>
            @forelse ($pendingListings as $listing)
                <tr class="border-b border-gray-50 hover:bg-gray-50"><td class="td"><p class="font-semibold">{{ $listing->title }}</p><p class="text-xs text-ink-500">{{ $listing->project?->name }} · {{ $listing->city }}</p></td>
                <td class="td">{{ $listing->seller->name }}</td><td class="td font-semibold">{{ $egp($listing->paid_amount) }}</td>
                <td class="td text-end"><a class="btn-primary" href="{{ route('admin.listings.show', $listing) }}">{{ __('admin.review') }}</a></td></tr>
            @empty <tr><td class="td" colspan="4">{{ __('admin.no_pending') }} 🎉</td></tr> @endforelse
            </tbody></table></div>
    </div>

    <div class="card">
        <div class="border-b border-gray-100 px-5 py-4"><h2 class="font-bold">{{ __('admin.deals_by_stage') }}</h2></div>
        <div class="flex flex-col gap-3 p-5">
            @foreach (\App\Enums\DealStage::cases() as $stage)
                @php $total = $dealsByStage[$stage->value] ?? 0; @endphp
                <div><div class="mb-1 flex justify-between text-sm"><span>{{ $stage->label() }}</span><span class="font-bold">{{ $total }}</span></div>
                <div class="h-2 rounded-full bg-gray-100"><div class="h-2 rounded-full bg-brand-500" style="width: {{ min(100, $total * 10) }}%"></div></div></div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid gap-6 xl:grid-cols-2">
    <div class="card">
        <div class="border-b border-gray-100 px-5 py-4"><h2 class="font-bold">{{ __('admin.recent_deals') }}</h2></div>
        <div class="flex flex-col divide-y divide-gray-50">
            @forelse ($recentDeals as $deal)
                <a href="{{ route('admin.deals.show', $deal) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                    <div><p class="text-sm font-semibold">#{{ $deal->id }} — {{ $deal->listing->title }}</p><p class="text-xs text-ink-500">{{ $deal->buyer->name }} ← {{ $deal->seller->name }}</p></div>
                    <span class="badge bg-sky-100 text-sky-800">{{ $deal->stage->label() }}</span></a>
            @empty <p class="px-5 py-4 text-sm text-ink-500">No deals yet.</p> @endforelse
        </div>
    </div>
    <div class="card">
        <div class="border-b border-gray-100 px-5 py-4"><h2 class="font-bold">{{ __('admin.latest_evaluations') }}</h2></div>
        <div class="flex flex-col divide-y divide-gray-50">
            @forelse ($recentEvaluations as $ev)
                <a href="{{ route('admin.evaluations.show', $ev) }}" class="flex items-center justify-between gap-3 px-5 py-3 hover:bg-gray-50">
                    <div><p class="text-sm font-semibold">{{ $ev->name }} · {{ $ev->phone }}</p><p class="text-xs text-ink-500">Paid {{ $egp($ev->paid_amount) }} of {{ $egp($ev->total_price) }}</p></div>
                    <span class="badge bg-amber-100 text-amber-800">{{ $ev->status }}</span></a>
            @empty <p class="px-5 py-4 text-sm text-ink-500">No evaluations yet.</p> @endforelse
        </div>
    </div>
</div>
@endsection
