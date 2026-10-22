<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->can('audit.view'), 403);
        $logs = AuditLog::with('user')
            ->when($request->input('action'), fn ($q, $v) => $q->where('action', 'like', "%{$v}%"))
            ->latest()->paginate(25)->withQueryString();

        return view('admin.audit-logs.index', compact('logs'));
    }
}
