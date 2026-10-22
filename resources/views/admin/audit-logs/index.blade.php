@extends('layouts.admin')
@section('title', __('admin.audit_logs'))
@section('content')
<form method="GET" class="card flex items-end gap-3 p-4"><div class="flex-1"><label class="label">{{ __('admin.action_contains') }}</label><input name="action" class="input" value="{{ request('action') }}" placeholder="listing.status_changed"></div><button class="btn-primary"><x-icon name="search" class="h-4 w-4" /> Filter</button></form>
<div class="card overflow-x-auto"><table class="w-full min-w-[760px]"><thead><tr><th class="th">{{ __('admin.when') }}</th><th class="th">{{ __('admin.actor') }}</th><th class="th">{{ __('admin.action') }}</th><th class="th">{{ __('admin.subject') }}</th><th class="th">{{ __('admin.change') }}</th><th class="th">IP</th></tr></thead><tbody>
@forelse($logs as $log)
<tr class="border-b border-gray-50"><td class="td whitespace-nowrap">{{ $log->created_at }}</td><td class="td">{{ $log->user?->name ?? 'system' }}</td><td class="td"><span class="badge bg-gray-100 text-gray-700">{{ $log->action }}</span></td>
<td class="td">{{ $log->auditable_type ? class_basename($log->auditable_type).' #'.$log->auditable_id : '—' }}</td>
<td class="td"><code class="text-xs">{{ json_encode($log->old_values) }} → {{ json_encode($log->new_values) }}</code></td><td class="td">{{ $log->ip_address }}</td></tr>
@empty <tr><td class="td" colspan="6">No audit entries yet.</td></tr> @endforelse
</tbody></table></div>
{{ $logs->links() }}
@endsection
