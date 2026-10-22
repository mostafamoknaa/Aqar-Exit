@extends('layouts.web')
@section('title', 'Evaluate My Contract')
@section('content')
<div class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
    <h1 class="text-3xl font-bold">Know your position in a minute — free</h1>
    <p class="mt-2 text-ink-500">Enter your unit price and what you paid, and see the difference between cancelling with the developer and assigning through us.</p>

    <div class="mt-8 grid gap-6 lg:grid-cols-2">
        <form method="POST" action="{{ route('web.evaluate.calculate') }}" class="card flex flex-col gap-4 p-6">
            @csrf <h2 class="flex items-center gap-2 font-bold"><x-icon name="calculator" class="text-brand-600" /> Calculator</h2>
            <div><label class="label">Total unit price (EGP)</label><input name="total_price" type="number" min="1" class="input" value="{{ $input['total_price'] ?? '' }}" required></div>
            <div><label class="label">Amount already paid (EGP)</label><input name="paid_amount" type="number" min="0" class="input" value="{{ $input['paid_amount'] ?? '' }}" required></div>
            <div><label class="label">Developer cancellation % (default {{ \App\Models\Setting::get('default_cancellation_percent', 15) }}%)</label><input name="cancellation_percent" type="number" step="0.1" min="0" max="100" class="input" value="{{ $input['cancellation_percent'] ?? '' }}"></div>
            <button class="btn-primary">Calculate</button>
            @isset($result)
            <div class="grid gap-3">
                <div class="rounded-xl bg-red-50 p-4"><p class="font-bold text-red-700">Return to developer</p><p class="text-sm">Penalty {{ number_format($result['cancellation_penalty']) }} EGP · you get back {{ number_format($result['refund_if_cancelled']) }} EGP (lose {{ $result['loss_percent_of_paid'] }}% of what you paid)</p></div>
                <div class="rounded-xl bg-emerald-50 p-4"><p class="font-bold text-emerald-700">Assign through us</p><p class="text-sm">Recover {{ number_format($result['refund_with_aqar_exit']) }} EGP cash — 100% of what you paid. Seller commission: zero.</p></div>
            </div>
            @endisset
        </form>

        <form method="POST" action="{{ route('web.evaluate.store') }}" class="card flex flex-col gap-4 p-6">
            @csrf <h2 class="flex items-center gap-2 font-bold"><x-icon name="document" class="text-brand-600" /> Request my free exit report</h2>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label">Name</label><input name="name" class="input" required></div>
                <div><label class="label">Phone</label><input name="phone" class="input" required></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label">Developer</label><input name="developer_name" class="input"></div>
                <div><label class="label">Project / Compound</label><input name="project_name" class="input"></div>
            </div>
            <div><label class="label">City</label><input name="city" class="input"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="label">Total price</label><input name="total_price" type="number" class="input" value="{{ $input['total_price'] ?? '' }}" required></div>
                <div><label class="label">Paid amount</label><input name="paid_amount" type="number" class="input" value="{{ $input['paid_amount'] ?? '' }}" required></div>
            </div>
            <button class="btn-primary">Send Request</button>
            <p class="text-xs text-ink-500">No commitment. A case manager reviews your contract position and contacts you.</p>
        </form>
    </div>
</div>
@endsection
