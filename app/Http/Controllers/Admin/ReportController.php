<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DealStage;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Evaluation;
use App\Models\Interest;
use App\Models\Listing;
use App\Models\User;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index', ['report' => $this->data()]);
    }

    public function export(): StreamedResponse
    {
        abort_unless(auth()->user()->can('reports.export'), 403);
        $data = $this->data();

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['metric', 'value']);
            foreach ($data as $k => $v) {
                if (is_scalar($v)) {
                    fputcsv($out, [$k, $v]);
                }
            }
            fclose($out);
        }, 'aqar-report-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function data(): array
    {
        $completed = Deal::where('stage', DealStage::Completed);

        return [
            'listings_total' => Listing::count(),
            'listings_published' => Listing::published()->count(),
            'listings_pending' => Listing::where('status', 'pending_review')->count(),
            'listings_rejected' => Listing::where('status', 'rejected')->count(),
            'financial_verified' => Listing::where('financial_status', 'verified')->count(),
            'financial_discrepancy' => Listing::where('financial_status', 'discrepancy')->count(),
            'financial_unverified' => Listing::where('financial_status', 'unverified')->count(),
            'buyers' => User::where('role', 'user')->whereHas('interests')->distinct()->count(),
            'sellers' => User::whereHas('listings')->distinct()->count(),
            'users_total' => User::count(),
            'interests_total' => Interest::count(),
            'evaluations_total' => Evaluation::count(),
            'deals_total' => Deal::count(),
            'deals_completed' => (clone $completed)->count(),
            'commission_earned' => (string) (clone $completed)->sum('commission_amount'),
            'avg_deal_days' => (clone $completed)->get()->avg(fn (Deal $d) => $d->created_at->diffInDays($d->completed_at ?? now())),
            'interests_by_status' => Interest::selectRaw('status, count(*) t')->groupBy('status')->pluck('t', 'status'),
            'deals_by_stage' => Deal::selectRaw('stage, count(*) t')->groupBy('stage')->pluck('t', 'stage'),
            'listings_by_status' => Listing::selectRaw('status, count(*) t')->groupBy('status')->pluck('t', 'status'),
        ];
    }
}
