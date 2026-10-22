@extends('layouts.admin')
@section('title', 'Evaluation #'.$evaluation->id)
@section('content')
@php $r = $evaluation->result(); $egp = fn($n)=>number_format($n).' EGP'; @endphp
<div class="grid gap-6 xl:grid-cols-2">
    <div class="card flex flex-col gap-4 p-5">
        <h2 class="font-bold">{{ $evaluation->name }} · {{ $evaluation->phone }}</h2>
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-lg bg-red-50 p-4"><p class="text-xs text-ink-500">{{ __('admin.if_returned') }}</p><p class="font-bold text-red-700">Penalty {{ $egp($r['cancellation_penalty']) }}</p><p class="text-sm">Refund {{ $egp($r['refund_if_cancelled']) }} — loss {{ $r['loss_percent_of_paid'] }}% of paid</p></div>
            <div class="rounded-lg bg-emerald-50 p-4"><p class="text-xs text-ink-500">With Aqar Exit</p><p class="font-bold text-emerald-700">{{ $egp($r['refund_with_aqar_exit']) }} cash</p><p class="text-sm">100% of paid recovered, seller fee 0</p></div>
        </div>
        <p class="text-sm text-ink-500">{{ $evaluation->developer_name }} · {{ $evaluation->project_name }} · {{ $evaluation->city }} · Total {{ $egp($evaluation->total_price) }} · Paid {{ $egp($evaluation->paid_amount) }} · Cancellation {{ $evaluation->cancellation_percent }}%</p>
    </div>
    <form method="POST" action="{{ route('admin.evaluations.update', $evaluation) }}" class="card flex flex-col gap-3 p-5">@csrf @method('PATCH')
        <h3 class="font-bold">{{ __('admin.follow_up_title') }}</h3>
        <div><label class="label">{{ __('admin.status') }}</label><select name="status" class="input">@foreach(['new','contacted','converted','closed'] as $s)<option @selected($evaluation->status===$s)>{{ $s }}</option>@endforeach</select></div>
        <div><label class="label">{{ __('admin.admin_notes') }}</label><textarea name="admin_notes" rows="4" class="input">{{ $evaluation->admin_notes }}</textarea></div>
        <button class="btn-primary">{{ __('admin.save') }}</button>
        <p class="text-xs text-ink-500">Converted = seller created a listing (do it with him in the app, then mark here).</p>
    </form>
</div>
@endsection
