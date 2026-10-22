@extends('layouts.admin')
@section('title', 'Deal #'.$deal->id)
@section('content')
@php $egp = fn($n) => number_format($n).' EGP'; @endphp
<div class="grid gap-6 xl:grid-cols-3">
    <div class="card flex flex-col gap-4 p-5 xl:col-span-2">
        <div><h2 class="font-bold">{{ $deal->listing->title }}</h2><p class="text-sm text-ink-500">{{ $deal->listing->project?->name }} · {{ $deal->listing->city }}</p></div>
        <div>
            <div class="mb-2 flex justify-between text-sm"><span>Progress</span><span class="font-bold">{{ $deal->progress_percent }}%</span></div>
            <div class="h-3 rounded-full bg-gray-100"><div class="h-3 rounded-full bg-brand-500" style="width:{{ $deal->progress_percent }}%"></div></div>
            <div class="mt-2 flex flex-wrap gap-1">@foreach(\App\Enums\DealStage::pipeline() as $s)<span class="badge {{ $s===$deal->stage ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $s->label() }}</span>@endforeach</div>
        </div>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach([['Buyer', $deal->buyer->name.' · '.$deal->buyer->phone],['Seller', $deal->seller->name.' · '.$deal->seller->phone],['Cash to seller', $egp($deal->cash_to_seller)],['Total price', $egp($deal->total_price)],['Commission ('.($deal->commission_rate*100).'%)', $egp($deal->commission_amount)],['Signing date', $deal->expected_signing_date?->toDateString() ?? '—'],['Case manager', $deal->caseManager?->name ?? 'Unassigned'],['Created', $deal->created_at->toDateString()]] as [$k,$v])
                <div class="rounded-lg bg-gray-50 p-3"><p class="text-xs text-ink-500">{{ $k }}</p><p class="font-semibold">{{ $v }}</p></div>
            @endforeach
        </div>
        @if($deal->notes)<div class="rounded-lg bg-amber-50 p-4 text-sm whitespace-pre-line">{{ $deal->notes }}</div>@endif
        <a class="btn-secondary w-fit" href="{{ route('admin.listings.show', $deal->listing) }}">Open listing & documents →</a>
    </div>

    <form method="POST" action="{{ route('admin.deals.update', $deal) }}" class="card flex flex-col gap-3 p-5">
        @csrf @method('PATCH') <h3 class="font-bold">Move Case Forward</h3>
        <div><label class="label">Stage</label><select name="stage" class="input">@foreach($stages as $s)<option value="{{ $s->value }}" @selected($deal->stage===$s)>{{ $s->label() }}</option>@endforeach</select></div>
        <div><label class="label">Case Manager</label><select name="case_manager_id" class="input"><option value="">Unassigned</option>@foreach($managers as $m)<option value="{{ $m->id }}" @selected($deal->case_manager_id===$m->id)>{{ $m->name }}</option>@endforeach</select></div>
        <div><label class="label">Expected signing date</label><input type="date" name="expected_signing_date" class="input" value="{{ $deal->expected_signing_date?->toDateString() }}"></div>
        <div><label class="label">Notes</label><textarea name="notes" rows="4" class="input">{{ $deal->notes }}</textarea></div>
        <button class="btn-primary">Save</button>
        <p class="text-xs text-ink-500">Completing a deal marks the listing as Completed. Cancelling re-publishes it.</p>
    </form>
</div>
@endsection
