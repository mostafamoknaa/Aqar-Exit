<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ListingResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FavoriteController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ListingResource::collection(
            $request->user()->favoriteListings()->published()->with(['images', 'project', 'developer'])->latest('favorites.created_at')->paginate(15)
        );
    }

    public function toggle(Request $request, Listing $listing): JsonResponse
    {
        $result = $request->user()->favoriteListings()->toggle($listing->id);

        return response()->json(['data' => ['is_favorite' => count($result['attached']) > 0]]);
    }
}
