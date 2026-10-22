<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use App\Models\PropertyCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $listings = Listing::published()->with(['images', 'category', 'project.developer'])
            ->filter($request->only(['search', 'city', 'governorate', 'district', 'unit_type', 'category_id', 'project_id', 'developer_id', 'min_paid', 'max_paid', 'min_total', 'max_total', 'bedrooms', 'min_area', 'max_area']))
            ->when($request->input('sort') === 'cash_low', fn ($q) => $q->orderBy('paid_amount'))
            ->when($request->input('sort') === 'total_low', fn ($q) => $q->orderBy('total_price'))
            ->when(! $request->filled('sort'), fn ($q) => $q->latest('published_at'))
            ->paginate(12)->withQueryString();

        return view('web.listings.index', [
            'listings' => $listings,
            'categories' => PropertyCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'cities' => Listing::published()->distinct()->orderBy('city')->pluck('city'),
            'governorates' => Listing::published()->whereNotNull('governorate')->distinct()->pluck('governorate'),
        ]);
    }

    public function show(Listing $listing): View
    {
        abort_unless($listing->status->value === 'published', 404);
        $listing->increment('views_count');

        return view('web.listings.show', [
            'listing' => $listing->load(['images', 'category', 'project.developer', 'developer']),
            'similar' => Listing::published()->with(['images', 'category'])
                ->where('id', '!=', $listing->id)->where('city', $listing->city)->latest('published_at')->limit(3)->get(),
        ]);
    }
}
