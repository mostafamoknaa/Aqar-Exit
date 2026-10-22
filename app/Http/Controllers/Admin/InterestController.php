<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Interest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterestController extends Controller
{
    public function index(Request $request): View
    {
        $interests = Interest::with(['listing.project', 'buyer'])
            ->when($request->input('status'), fn ($q, $v) => $q->where('status', $v))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.interests.index', compact('interests'));
    }

    public function update(Request $request, Interest $interest): RedirectResponse
    {
        $interest->update($request->validate(['status' => ['required', 'in:new,contacted,qualified,converted,rejected'], 'admin_notes' => ['nullable', 'string']]));

        return back()->with('success', 'Interest updated.');
    }
}
