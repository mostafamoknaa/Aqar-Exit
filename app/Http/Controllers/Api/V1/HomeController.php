<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DeveloperResource;
use App\Http\Resources\V1\ListingResource;
use App\Models\Developer;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;

class HomeController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $published = Listing::published();

        return response()->json(['data' => [
            'stats' => [
                'published_listings' => (clone $published)->count(),
                'total_market_value' => (int) (clone $published)->sum('market_price'),
                'cities' => (clone $published)->distinct()->count('city'),
            ],
            'featured_listings' => ListingResource::collection(
                (clone $published)->with(['images', 'project.developer', 'developer'])->where('is_featured', true)->latest('published_at')->limit(10)->get()
            ),
            'latest_listings' => ListingResource::collection(
                (clone $published)->with(['images', 'project.developer', 'developer'])->latest('published_at')->limit(10)->get()
            ),
            'developers' => DeveloperResource::collection(
                Developer::where('is_active', true)->withCount(['listings' => fn ($q) => $q->published()])->limit(12)->get()
            ),
            'commission' => [
                'seller_rate' => config('aqar.seller_commission_rate'),
                'buyer_rate' => config('aqar.buyer_commission_rate'),
            ],
        ]]);
    }
}
