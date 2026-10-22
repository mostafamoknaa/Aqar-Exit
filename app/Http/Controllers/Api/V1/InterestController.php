<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\InterestResource;
use App\Models\Listing;
use App\Notifications\NewInterestReceived;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InterestController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return InterestResource::collection(
            $request->user()->interests()->with(['listing.images', 'listing.project'])->latest()->paginate(15)
        );
    }

    public function store(Request $request, Listing $listing): JsonResponse
    {
        abort_unless($listing->status->value === 'published', 422, 'Listing is not available.');
        abort_if($listing->seller_id === $request->user()->id, 422, 'You cannot express interest in your own listing.');
        $validated = $request->validate([
            'message' => ['nullable', 'string', 'max:1000'],
            'cash_available' => ['nullable', 'numeric', 'min:0'],
        ]);
        $interest = $listing->interests()->updateOrCreate(['buyer_id' => $request->user()->id], $validated);
        if ($interest->wasRecentlyCreated) {
            $listing->seller->notify(new NewInterestReceived($listing->id, $interest->id));
        }

        return response()->json(['data' => new InterestResource($interest)], $interest->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request, int $interest): JsonResponse
    {
        $request->user()->interests()->whereKey($interest)->delete();

        return response()->json(['message' => 'Interest withdrawn.']);
    }
}
