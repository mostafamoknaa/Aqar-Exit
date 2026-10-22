@extends('layouts.admin')
@section('title', 'Review Listing #'.$listing->id)
@section('content')
@php $egp = fn($n) => $n === null ? '—' : number_format($n).' EGP'; @endphp
<div class="flex flex-wrap items-center gap-3">
    <span class="badge {{ $listing->status->badgeColor() }}">{{ $listing->status->label() }}</span>
    @if($listing->is_silent)<span class="badge bg-gray-100 text-gray-700">Silent exit</span>@endif
    <form method="POST" action="{{ route('admin.listings.featured', $listing) }}">@csrf <button class="btn-secondary">{{ $listing->is_featured ? 'Unfeature' : '⭐ Feature' }}</button></form>
    <form method="POST" action="{{ route('admin.listings.destroy', $listing) }}" onsubmit="return confirm('Delete this listing?')">@csrf @method('DELETE') <button class="btn-danger">{{ __('admin.delete') }}</button></form>
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <div class="card flex flex-col gap-4 p-5 xl:col-span-2">
        <div><h2 class="text-lg font-bold">{{ $listing->title }}</h2><p class="text-sm text-ink-500">{{ $listing->project?->name }} · {{ $listing->developer?->name }} · {{ $listing->city }} {{ $listing->district }}</p></div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach ([['Type', $listing->unit_type->label()], ['Area', $listing->area_sqm.' m²'], ['Bed/Bath', $listing->bedrooms.' / '.$listing->bathrooms], ['Floor', $listing->floor ?? '—'], ['Unit No. (private)', $listing->unit_number ?? '—'], ['Contract', $listing->contract_date?->toDateString() ?? '—'], ['Delivery', $listing->delivery_date?->toDateString() ?? '—'], ['Seller', $listing->seller->name.' · '.$listing->seller->phone]] as [$k,$v])
                <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-ink-500">{{ $k }}</p><p class="font-semibold">{{ $v }}</p></div>
            @endforeach
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-lg bg-brand-50 p-4"><p class="text-xs text-ink-500">Cash to seller (= paid, no over)</p><p class="text-xl font-bold text-brand-700">{{ $egp($listing->paid_amount) }}</p></div>
            <div class="rounded-lg bg-gray-50 p-4"><p class="text-xs text-ink-500">{{ __('admin.total_old_contract') }}</p><p class="text-xl font-bold">{{ $egp($listing->total_price) }}</p></div>
            <div class="rounded-lg bg-gray-50 p-4"><p class="text-xs text-ink-500">{{ __('admin.remaining_installments') }}</p><p class="font-bold">{{ $egp($listing->remaining_amount) }} @if($listing->installment_amount) · {{ $egp($listing->installment_amount) }} × {{ $listing->installments_remaining }} @endif</p></div>
            <div class="rounded-lg bg-gray-50 p-4"><p class="text-xs text-ink-500">Est. market today / buyer saving</p><p class="font-bold">{{ $egp($listing->market_price) }} / <span class="text-emerald-600">{{ $egp($listing->saving_vs_market) }}</span></p></div>
        </div>
        @if($listing->images->count())
            <div class="flex flex-wrap gap-2">@foreach($listing->images as $img)<img src="{{ $img->url }}" class="h-24 w-32 rounded-lg object-cover" alt="">@endforeach</div>
        @endif
    </div>

    <div class="flex flex-col gap-6">
        <form method="POST" action="{{ route('admin.listings.status', $listing) }}" class="card flex flex-col gap-3 p-5">
            @csrf @method('PATCH') <h3 class="font-bold">{{ __('admin.verification_decision') }}</h3>
            <div><label class="label">Next workflow step (current: {{ $listing->status->label() }} · case step {{ \App\Services\CaseWorkflow::currentStep($listing->status->value, $listing->deals->first()?->stage->value) }}/12)</label>
            <select name="status" class="input">@php $svc = app(\App\Services\ListingTransitionService::class); @endphp @foreach(\App\Enums\ListingStatus::cases() as $s)@if($svc->canTransition($listing, $s))<option value="{{ $s->value }}">{{ $s->label() }}</option>@endif @endforeach</select>
            <p class="mt-1 text-xs text-ink-500">Only valid transitions from the current step are shown — the server rejects invalid jumps.</p></div>
            <div><label class="label">{{ __('admin.notes_to_seller') }}</label><textarea name="verification_notes" class="input" rows="3">{{ $listing->verification_notes }}</textarea></div>
            <button class="btn-primary">{{ __('admin.apply') }}</button>
        </form>

        <form method="POST" action="{{ route('admin.listings.verify-financials', $listing) }}" class="card flex flex-col gap-3 p-5">
            @csrf @method('PATCH') <h3 class="flex items-center gap-2 font-bold"><x-icon name="calculator" class="text-brand-600" /> Financial Verification</h3>
            <p class="text-xs text-ink-500">Status: <span class="badge {{ $listing->financial_status === 'verified' ? 'bg-emerald-100 text-emerald-800' : ($listing->financial_status === 'discrepancy' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $listing->financial_status }}</span>
            @if($listing->financial_verified_at) · {{ $listing->financialVerifier?->name }} · {{ $listing->financial_verified_at }} @endif</p>
            <p class="text-xs text-ink-500">Reported: total {{ number_format((float)$listing->total_price) }} · paid {{ number_format((float)$listing->paid_amount) }}. Enter figures read from documents — a mismatch flags discrepancy, it does not overwrite reported values.</p>
            <div><label class="label">{{ __('admin.verified_total') }}</label><input name="verified_total_price" type="number" step="0.01" class="input" value="{{ $listing->verified_total_price }}" required></div>
            <div><label class="label">{{ __('admin.verified_paid') }}</label><input name="verified_paid_amount" type="number" step="0.01" class="input" value="{{ $listing->verified_paid_amount }}" required></div>
            <div><label class="label">{{ __('admin.notes_evidence') }}</label><textarea name="financial_notes" class="input" rows="2">{{ $listing->financial_notes }}</textarea></div>
            <button class="btn-primary">{{ __('admin.record_verification') }}</button>
        </form>

        <div class="card p-5">
            <h3 class="mb-3 font-bold">{{ __('admin.documents_private') }}</h3>
            <div class="flex flex-col gap-2">
            @forelse ($listing->documents as $doc)
                <div class="flex flex-wrap items-center gap-2 rounded-lg bg-gray-50 p-3 text-sm">
                    <span class="font-semibold">{{ $doc->type }}</span><span class="badge {{ $doc->status==='verified' ? 'bg-emerald-100 text-emerald-800' : ($doc->status==='rejected' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ $doc->status }}</span>
                    <a class="text-brand-700 underline" href="{{ route('admin.listings.documents.download', $doc) }}">{{ __('admin.download') }}</a>
                    @can('documents.review')<a class="text-ink-500 underline" target="_blank" href="{{ \Illuminate\Support\Facades\URL::temporarySignedRoute('documents.signed-download', now()->addMinutes(5), ['document' => $doc->id]) }}" title="Expires in 5 minutes">Signed link (5m)</a>@endcan
                    <form method="POST" action="{{ route('admin.listings.documents.review', $doc) }}" class="flex gap-1">@csrf @method('PATCH')<button name="status" value="verified" class="btn-secondary !px-2 !py-1">{{ __('admin.verify') }}</button><button name="status" value="rejected" class="btn-secondary !px-2 !py-1">{{ __('admin.reject') }}</button></form>
                </div>
            @empty <p class="text-sm text-ink-500">No documents uploaded yet.</p> @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card overflow-x-auto">
    <div class="border-b border-gray-100 px-5 py-4"><h3 class="font-bold">Buyer Interests — convert a qualified buyer into a Deal</h3></div>
    <table class="w-full min-w-[640px]"><thead><tr><th class="th">{{ __('admin.buyer') }}</th><th class="th">{{ __('admin.cash_available') }}</th><th class="th">{{ __('admin.status') }}</th><th class="th">{{ __('admin.message') }}</th><th class="th"></th></tr></thead><tbody>
    @forelse ($listing->interests as $interest)
        <tr class="border-b border-gray-50"><td class="td">{{ $interest->buyer->name }}<p class="text-xs">{{ $interest->buyer->phone }}</p></td><td class="td">{{ $egp($interest->cash_available) }}</td><td class="td">{{ $interest->status }}</td><td class="td">{{ $interest->message }}</td>
        <td class="td text-end"><form method="POST" action="{{ route('admin.listings.convert', $listing) }}">@csrf <input type="hidden" name="interest_id" value="{{ $interest->id }}"><button class="btn-primary" @disabled($interest->status==='converted')>{{ __('admin.create_deal') }}</button></form></td></tr>
        <tr class="border-b border-gray-100 bg-gray-50/60"><td colspan="5" class="px-4 py-3">
            <details><summary class="cursor-pointer text-sm font-semibold">Thread with {{ $interest->buyer->name }} ({{ $interest->messages->count() }} messages)</summary>
                <div class="mt-3 flex max-w-2xl flex-col gap-2">
                    @foreach($interest->messages as $m)
                        <div class="rounded-lg {{ $m->sender_role==='staff' ? 'bg-brand-50 self-end' : 'bg-white' }} border border-gray-100 p-3 text-sm"><p>{{ $m->body }}</p><p class="mt-1 text-xs text-ink-500">{{ $m->sender?->name }} · {{ $m->sender_role }} · {{ $m->created_at }}</p></div>
                    @endforeach
                    <form method="POST" action="{{ route('admin.interests.reply', $interest) }}" class="flex gap-2">@csrf <input name="body" class="input" placeholder="Reply as staff…" required><button class="btn-primary">{{ __('admin.send') }}</button></form>
                </div></details>
        </td></tr>
    @empty <tr><td class="td" colspan="5">No interests yet.</td></tr> @endforelse
    </tbody></table>
</div>
@endsection
