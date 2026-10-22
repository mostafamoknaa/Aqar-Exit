@extends('layouts.admin')
@section('title', 'Exit Evaluations')
@section('content')
<form method="GET" class="card flex flex-wrap items-end gap-3 p-4"><div><label class="label">Status</label><select name="status" class="input"><option value="">All</option>@foreach(['new','contacted','converted','closed'] as $s)<option @selected(request('status')===$s)>{{ $s }}</option>@endforeach</select></div><button class="btn-primary">Filter</button></form>
<div class="card overflow-x-auto"><table class="w-full min-w-[720px]"><thead><tr><th class="th">Lead</th><th class="th">Unit</th><th class="th">Total / Paid</th><th class="th">Loss if cancelled</th><th class="th">Status</th><th class="th"></th></tr></thead><tbody>
@forelse($evaluations as $ev) @php $r = $ev->result(); @endphp
<tr class="border-b border-gray-50 hover:bg-gray-50"><td class="td"><p class="font-semibold">{{ $ev->name }}</p><p class="text-xs">{{ $ev->phone }}</p></td><td class="td">{{ $ev->developer_name }} · {{ $ev->project_name }}<p class="text-xs">{{ $ev->city }}</p></td>
<td class="td">{{ number_format($ev->total_price) }} / {{ number_format($ev->paid_amount) }}</td><td class="td text-red-600 font-semibold">-{{ number_format($r['loss_if_cancelled']) }} EGP ({{ $r['loss_percent_of_paid'] }}%)</td>
<td class="td"><span class="badge bg-amber-100 text-amber-800">{{ $ev->status }}</span></td><td class="td text-end"><a class="btn-secondary" href="{{ route('admin.evaluations.show', $ev) }}">Follow up</a></td></tr>
@empty <tr><td class="td" colspan="6">No evaluations.</td></tr> @endforelse
</tbody></table></div>
{{ $evaluations->links() }}
@endsection
