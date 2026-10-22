@extends('layouts.admin')
@section('title', __('admin.reports'))
@section('content')
<div class="flex justify-end">@if(auth()->user()->isAdmin())<a class="btn-primary" href="{{ route('admin.reports.export') }}"><x-icon name="download" class="h-4 w-4" /> Export CSV</a>@endif</div>
<div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach ([
        ['Listings total', $report['listings_total']], ['Published', $report['listings_published']], ['Pending review', $report['listings_pending']], ['Rejected', $report['listings_rejected']],
        ['Financial: verified', $report['financial_verified']], ['Financial: discrepancy', $report['financial_discrepancy']], ['Financial: unverified', $report['financial_unverified']], ['Users / sellers / buyers', $report['users_total'].' / '.$report['sellers'].' / '.$report['buyers']],
        ['Inquiries', $report['interests_total']], ['Evaluations', $report['evaluations_total']], ['Cases completed', $report['deals_completed']], ['Commission earned (EGP)', number_format((float)$report['commission_earned'], 2)],
    ] as [$label, $value])
        <div class="card p-5"><p class="text-sm text-ink-500">{{ $label }}</p><p class="mt-1 text-2xl font-bold">{{ $value }}</p></div>
    @endforeach
</div>
<p class="text-sm text-ink-500">Average completed-case processing: {{ $report['avg_deal_days'] ? round((float)$report['avg_deal_days'], 1).' days' : '—' }} · Financial totals show verification <em>status counts</em>, not verified money totals.</p>
<div class="grid gap-6 xl:grid-cols-3">
    @foreach ([['Listings by status', $report['listings_by_status']], ['Inquiries by status', $report['interests_by_status']], ['Cases by stage', $report['deals_by_stage']]] as [$title, $rows])
        <div class="card p-5"><h3 class="mb-3 font-bold">{{ $title }}</h3><div class="flex flex-col gap-2">@forelse($rows as $k=>$v)<div class="flex justify-between text-sm"><span>{{ $k }}</span><span class="font-bold">{{ $v }}</span></div>@empty <p class="text-sm text-ink-500">No data.</p>@endforelse</div></div>
    @endforeach
</div>
@endsection
