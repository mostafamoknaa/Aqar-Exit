<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DealResource;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DealController extends Controller
{
    /** Deals where the user is buyer OR seller, with stage progress for the tracker UI. */
    public function index(Request $request): AnonymousResourceCollection
    {
        $deals = Deal::where(fn ($q) => $q->where('buyer_id', $request->user()->id)->orWhere('seller_id', $request->user()->id))
            ->with(['listing.images', 'listing.project', 'seller', 'buyer', 'caseManager'])
            ->latest()
            ->paginate(15);

        return DealResource::collection($deals);
    }

    public function show(Request $request, Deal $deal): DealResource
    {
        abort_unless(in_array($request->user()->id, [$deal->buyer_id, $deal->seller_id], true), 403);

        return new DealResource($deal->load(['listing.images', 'listing.project', 'seller', 'buyer', 'caseManager']));
    }
}
