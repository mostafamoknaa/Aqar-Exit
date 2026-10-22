@extends('layouts.admin')
@section('title', $user->name)
@section('content')
<div class="grid gap-6 xl:grid-cols-3">
    <div class="card flex flex-col gap-3 p-5">
        <h2 class="text-lg font-bold">{{ $user->name }}</h2>
        <p class="text-sm text-ink-500">{{ $user->phone }}<br>{{ $user->email }}<br>{{ $user->city }} · joined {{ $user->created_at->toDateString() }}</p>
        <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex gap-2">@csrf @method('PATCH')
            <select name="role" class="input">@foreach(\App\Enums\UserRole::cases() as $r)<option value="{{ $r->value }}" @selected($user->role===$r)>{{ $r->label() }}</option>@endforeach</select>
            <button class="btn-primary">Save role</button></form>
        <form method="POST" action="{{ route('admin.users.toggle', $user) }}">@csrf @method('PATCH')<button class="btn-danger w-full">{{ $user->is_active ? 'Disable account' : 'Enable account' }}</button></form>
        @if($user->buyerPreference)<div class="rounded-lg bg-gray-50 p-3 text-sm"><p class="font-bold">Buyer preference</p><p>Cash ≤ {{ number_format($user->buyerPreference->max_cash ?? 0) }} · Installment ≤ {{ number_format($user->buyerPreference->max_installment ?? 0) }}</p><p>{{ implode(', ', $user->buyerPreference->cities ?? []) }}</p></div>@endif
    </div>
    <div class="card p-5 xl:col-span-2">
        <h3 class="mb-3 font-bold">Selling ({{ $user->listings->count() }})</h3>
        <div class="flex flex-col gap-2">@forelse($user->listings as $l)<a class="flex justify-between rounded-lg bg-gray-50 p-3 text-sm hover:bg-gray-100" href="{{ route('admin.listings.show', $l) }}"><span>{{ $l->title }}</span><span class="badge {{ $l->status->badgeColor() }}">{{ $l->status->label() }}</span></a>@empty <p class="text-sm text-ink-500">No listings.</p>@endforelse</div>
        <h3 class="mt-6 mb-3 font-bold">Buying — interests & deals</h3>
        <div class="flex flex-col gap-2">@forelse($user->dealsAsBuyer as $d)<a class="flex justify-between rounded-lg bg-gray-50 p-3 text-sm" href="{{ route('admin.deals.show', $d) }}"><span>Deal #{{ $d->id }} — {{ $d->listing->title }}</span><span class="badge bg-sky-100 text-sky-800">{{ $d->stage->label() }}</span></a>@empty <p class="text-sm text-ink-500">No deals.</p>@endforelse</div>
    </div>
</div>
@endsection
