<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DealStage;
use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Evaluation;
use App\Models\Interest;
use App\Models\Listing;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $stats = [
            'listings_total' => Listing::count(),
            'listings_pending' => Listing::where('status', ListingStatus::PendingReview)->count(),
            'listings_published' => Listing::published()->count(),
            'users' => User::where('role', 'user')->count(),
            'deals_active' => Deal::whereNotIn('stage', [DealStage::Completed, DealStage::Cancelled])->count(),
            'deals_completed' => Deal::where('stage', DealStage::Completed)->count(),
            'evaluations_new' => Evaluation::where('status', 'new')->count(),
            'interests_new' => Interest::where('status', 'new')->count(),
            'commission_earned' => (int) Deal::where('stage', DealStage::Completed)->sum('commission_amount'),
            'market_value' => (int) Listing::published()->sum('market_price'),
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'pendingListings' => Listing::with(['seller', 'project'])->where('status', ListingStatus::PendingReview)->latest()->limit(6)->get(),
            'recentDeals' => Deal::with(['listing', 'buyer', 'seller', 'caseManager'])->latest()->limit(6)->get(),
            'recentEvaluations' => Evaluation::latest()->limit(5)->get(),
            'dealsByStage' => Deal::selectRaw('stage, count(*) as total')->groupBy('stage')->pluck('total', 'stage'),
        ]);
    }
}
