<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DealStage;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\User;
use App\Services\DealTransitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DealController extends Controller
{
    /** Pipeline board grouped by stage. */
    public function index(Request $request): View
    {
        $deals = Deal::with(['listing', 'buyer', 'seller', 'caseManager'])
            ->when($request->input('stage'), fn ($q, $v) => $q->where('stage', $v))
            ->when($request->input('case_manager_id'), fn ($q, $v) => $q->where('case_manager_id', $v))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.deals.index', [
            'deals' => $deals,
            'stages' => DealStage::cases(),
            'managers' => User::whereIn('role', ['admin', 'manager'])->get(),
            'board' => Deal::with(['listing', 'buyer'])->whereNotIn('stage', [DealStage::Completed, DealStage::Cancelled])->latest()->limit(40)->get()->groupBy(fn (Deal $d) => $d->stage->value),
        ]);
    }

    public function show(Deal $deal): View
    {
        $deal->load(['listing.documents', 'listing.project', 'seller', 'buyer', 'caseManager', 'interest', 'events.actor']);

        return view('admin.deals.show', ['deal' => $deal, 'stages' => DealStage::cases(), 'managers' => User::whereIn('role', ['admin', 'manager'])->get()]);
    }

    public function update(Request $request, Deal $deal, DealTransitionService $transitions): RedirectResponse
    {
        $validated = $request->validate([
            'stage' => ['required', 'in:'.implode(',', array_column(DealStage::cases(), 'value'))],
            'case_manager_id' => ['nullable', 'exists:users,id'],
            'expected_signing_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);
        $newStage = DealStage::from($validated['stage']);
        // Non-stage fields first; stage moves only through the validated workflow.
        $deal->update(collect($validated)->except(['stage', 'reason'])->all());
        if ($newStage !== $deal->stage) {
            $this->authorizeTransition($request, $deal);
            $transitions->transition($deal, $newStage, $request->user(), $validated['reason'] ?? null);
        }

        return back()->with('success', 'Deal updated.');
    }

    private function authorizeTransition(Request $request, Deal $deal): void
    {
        abort_unless($request->user()->can('deals.transition') && ($request->user()->isAdmin() || $deal->case_manager_id === $request->user()->id), 403, 'Only the assigned case manager or an admin can move this case.');
    }
}
