<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evaluation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function index(Request $request): View
    {
        $evaluations = Evaluation::with('user')
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.evaluations.index', compact('evaluations'));
    }

    public function show(Evaluation $evaluation): View
    {
        return view('admin.evaluations.show', ['evaluation' => $evaluation->load('user')]);
    }

    public function update(Request $request, Evaluation $evaluation): RedirectResponse
    {
        $evaluation->update($request->validate(['status' => ['required', 'in:new,contacted,converted,closed'], 'admin_notes' => ['nullable', 'string']]));

        return back()->with('success', 'Evaluation updated.');
    }
}
