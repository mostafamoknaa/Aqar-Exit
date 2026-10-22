<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\PropertyCategory;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('web.home', [
            'featured' => Listing::published()->with(['images', 'category', 'project.developer'])->where('is_featured', true)->latest('published_at')->limit(6)->get(),
            'latest' => Listing::published()->with(['images', 'category', 'project.developer'])->latest('published_at')->limit(6)->get(),
            'categories' => PropertyCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'developers' => Developer::where('is_active', true)->withCount(['listings' => fn ($q) => $q->published()])->limit(8)->get(),
            'stats' => [
                'listings' => Listing::published()->count(),
                'market_value' => (string) Listing::published()->sum('market_price'),
                'cities' => Listing::published()->distinct()->count('city'),
            ],
        ]);
    }
}
