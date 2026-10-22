@extends('layouts.admin')
@section('title', 'Deals / Assignment Cases')
@section('content')
{{-- Kanban pipeline --}}
<div class="grid gap-4 overflow-x-auto pb-2 md:grid-cols-2 xl:grid-cols-4">
    @foreach (\App\Enums\DealStage::pipeline() as $stage)
        <div class="card min-w-56 p-3">
            <h3 class="mb-3 px-1 text-sm font-bold">{{ $stage->label() }} <span class="text-ink-500">({{ ($board[$stage->value] ?? collect())->count() }})</span></h3>
            <div class="flex flex-col gap-2">
                @foreach ($board[$stage->value] ?? [] as $deal)
                    <a href="{{ route('admin.deals.show', $deal) }}" class="rounded-lg border border-gray-100 bg-gray-50 p-3 hover:border-brand-500">
                        <p class="text-sm font-semibold">#{{ $deal->id }} {{ Str::limit($deal->listing->title, 28) }}</p>
                        <p class="text-xs text-ink-500">{{ $deal->buyer->name }} · {{ number_format($deal->cash_to_seller) }} EGP cash</p>
                        <div class="mt-2 h-1.5 rounded-full bg-gray-200"><div class="h-1.5 rounded-full bg-brand-500" style="width:{{ $deal->progress_percent }}%"></div></div>
                    </a>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<form method="GET" class="card flex flex-wrap items-end gap-3 p-4">
    <div><label class="label">{{ __('admin.stage') }}</label><select name="stage" class="input"><option value="">All</option>@foreach($stages as $s)<option value="{{ $s->value }}" @selected(request('stage')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div><label class="label">{{ __('admin.case_manager') }}</label><select name="case_manager_id" class="input"><option value="">All</option>@foreach($managers as $m)<option value="{{ $m->id }}" @selected(request('case_manager_id')==$m->id)>{{ $m->name }}</option>@endforeach</select></div>
    <button class="btn-primary">{{ __('admin.filter') }}</button>
</form>

<div class="card overflow-x-auto"><table class="w-full min-w-[760px]"><thead><tr><th class="th">#</th><th class="th">{{ __('admin.listing') }}</th><th class="th">Buyer → Seller</th><th class="th">{{ __('admin.cash_commission') }}</th><th class="th">{{ __('admin.stage') }}</th><th class="th">{{ __('admin.manager') }}</th><th class="th"></th></tr></thead><tbody>
@forelse($deals as $deal)
<tr class="border-b border-gray-50 hover:bg-gray-50"><td class="td">{{ $deal->id }}</td>
<td class="td font-semibold">{{ $deal->listing->title }}</td>
<td class="td">{{ $deal->buyer->name }} → {{ $deal->seller->name }}</td>
<td class="td">{{ number_format($deal->cash_to_seller) }} / <span class="text-emerald-600">{{ number_format($deal->commission_amount) }} EGP</span></td>
<td class="td"><span class="badge bg-sky-100 text-sky-800">{{ $deal->stage->label() }}</span></td>
<td class="td">{{ $deal->caseManager?->name ?? '—' }}</td>
<td class="td text-end"><a class="btn-secondary" href="{{ route('admin.deals.show', $deal) }}">{{ __('admin.manage') }}</a></td></tr>
@empty <tr><td class="td" colspan="7">No deals.</td></tr> @endforelse
</tbody></table></div>
{{ $deals->links() }}
@endsection
