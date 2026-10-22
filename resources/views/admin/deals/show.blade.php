@extends('layouts.admin')
@section('title', 'Deal #'.$deal->id)
@section('content')
@php $egp = fn($n) => number_format($n).' EGP'; @endphp
<div class="grid gap-6 xl:grid-cols-3">
    <div class="card flex flex-col gap-4 p-5 xl:col-span-2">
        <div><h2 class="font-bold">{{ $deal->listing->title }}</h2><p class="text-sm text-ink-500">{{ $deal->listing->project?->name }} · {{ $deal->listing->city }}</p></div>
        <div>
            <div class="mb-2 flex justify-between text-sm"><span>{{ __('admin.progress') }}</span><span class="font-bold">{{ $deal->progress_percent }}%</span></div>
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
        <div>
            <h3 class="mb-2 font-bold">{{ __('admin.case_history') }}</h3>
            <div class="flex flex-col gap-2">
                @forelse($deal->events as $event)
                    <div class="flex gap-3 rounded-lg bg-gray-50 p-3 text-sm"><x-icon name="clipboard" class="text-brand-600" /><div><p><span class="font-semibold">{{ $event->from_stage ?? 'created' }} → {{ $event->to_stage }}</span> · {{ $event->actor?->name ?? 'system' }}</p><p class="text-xs text-ink-500">{{ $event->created_at }} @if($event->reason) · {{ $event->reason }} @endif</p></div></div>
                @empty <p class="text-sm text-ink-500">No transitions recorded yet.</p> @endforelse
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.deals.update', $deal) }}" class="card flex flex-col gap-3 p-5">
        @csrf @method('PATCH') <h3 class="font-bold">{{ __('admin.move_case_forward') }}</h3>
        <div><label class="label">{{ __('admin.stage') }}</label><select name="stage" class="input">@foreach($stages as $s)<option value="{{ $s->value }}" @selected($deal->stage===$s)>{{ $s->label() }}</option>@endforeach</select></div>
        <div><label class="label">{{ __('admin.case_manager') }}</label><select name="case_manager_id" class="input"><option value="">{{ __('admin.unassigned') }}</option>@foreach($managers as $m)<option value="{{ $m->id }}" @selected($deal->case_manager_id===$m->id)>{{ $m->name }}</option>@endforeach</select></div>
        <div><label class="label">{{ __('admin.expected_signing') }}</label><input type="date" name="expected_signing_date" class="input" value="{{ $deal->expected_signing_date?->toDateString() }}"></div>
        <div><label class="label">{{ __('admin.reason') }}</label><input name="reason" class="input" placeholder="Recorded in case history"></div>
        <div><label class="label">{{ __('admin.notes') }}</label><textarea name="notes" rows="4" class="input">{{ $deal->notes }}</textarea></div>
        <button class="btn-primary">{{ __('admin.save') }}</button>
        <p class="text-xs text-ink-500">Completing a deal marks the listing as Completed. Cancelling re-publishes it.</p>
    </form>
</div>
@endsection
