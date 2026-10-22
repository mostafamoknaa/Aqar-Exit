<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ListingDetailResource;
use App\Http\Resources\V1\ListingResource;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $listings = Listing::published()
            ->with(['images', 'project', 'developer'])
            ->filter($request->only(['search', 'city', 'district', 'unit_type', 'project_id', 'developer_id', 'min_paid', 'max_paid', 'min_total', 'max_total', 'bedrooms']))
            ->when($request->user('sanctum'), fn ($q) => $q->withExists(['favorites as is_favorite' => fn ($f) => $f->where('user_id', $request->user('sanctum')->id)]))
            ->when($request->input('sort') === 'cash_low', fn ($q) => $q->orderBy('paid_amount'))
            ->when($request->input('sort') === 'saving_high', fn ($q) => $q->orderByRaw('(COALESCE(market_price,total_price)-total_price) DESC'))
            ->when(! $request->filled('sort'), fn ($q) => $q->latest('published_at'))
            ->paginate(min((int) $request->input('per_page', 15), 50));

        return ListingResource::collection($listings);
    }

    public function show(Request $request, Listing $listing): ListingDetailResource
    {
        abort_unless($listing->status->value === 'published' || $listing->seller_id === $request->user('sanctum')?->id, 404);
        $listing->increment('views_count');
        $listing->load(['images', 'project.developer', 'developer', 'seller'])->loadCount('interests');
        if ($user = $request->user('sanctum')) {
            $listing->is_favorite = $listing->favorites()->where('user_id', $user->id)->exists();
        }

        return new ListingDetailResource($listing);
    }
}
