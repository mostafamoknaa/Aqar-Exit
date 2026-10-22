<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreListingRequest;
use App\Http\Resources\V1\ListingDetailResource;
use App\Http\Resources\V1\ListingResource;
use App\Models\Listing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class MyListingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ListingResource::collection(
            $request->user()->listings()->with(['images', 'project', 'developer'])->latest()->paginate(15)
        );
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['images', 'submit']);
        $data['status'] = $request->boolean('submit') ? ListingStatus::PendingReview : ListingStatus::Draft;
        $listing = $request->user()->listings()->create($data);
        $this->storeImages($request, $listing);

        return response()->json(['data' => new ListingDetailResource($listing->load(['images', 'project', 'developer']))], 201);
    }

    public function show(Request $request, Listing $listing): ListingDetailResource
    {
        $this->own($request, $listing);

        return new ListingDetailResource($listing->load(['images', 'project', 'developer', 'documents']));
    }

    public function update(StoreListingRequest $request, Listing $listing): ListingDetailResource
    {
        $this->own($request, $listing);
        abort_if(in_array($listing->status, [ListingStatus::Completed, ListingStatus::Reserved], true), 422, 'Listing is locked by an active deal.');
        $listing->update($request->safe()->except(['images', 'submit']));
        if ($request->boolean('submit')) {
            $listing->update(['status' => ListingStatus::PendingReview]);
        }
        $this->storeImages($request, $listing);

        return new ListingDetailResource($listing->fresh()->load(['images', 'project', 'developer']));
    }

    public function destroy(Request $request, Listing $listing): JsonResponse
    {
        $this->own($request, $listing);
        $listing->delete();

        return response()->json(['message' => 'Listing deleted.']);
    }

    /** Seller uploads contract / receipts. Files go to the PRIVATE disk and are only visible to staff + owner via API. */
    public function uploadDocument(Request $request, Listing $listing): JsonResponse
    {
        $this->own($request, $listing);
        $validated = $request->validate([
            'type' => ['required', 'in:contract,payment_receipt,id_card,developer_statement,other'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $path = $request->file('file')->store("listings/{$listing->id}/documents", config('aqar.document_disk'));
        $document = $listing->documents()->create([
            'uploaded_by' => $request->user()->id,
            'type' => $validated['type'],
            'original_name' => $request->file('file')->getClientOriginalName(),
            'path' => $path,
        ]);

        return response()->json(['data' => ['id' => $document->id, 'type' => $document->type, 'status' => $document->status]], 201);
    }

    public function submit(Request $request, Listing $listing): ListingResource
    {
        $this->own($request, $listing);
        abort_unless($listing->documents()->exists(), 422, 'Upload at least the contract or payment receipts before submitting.');
        $listing->update(['status' => ListingStatus::PendingReview]);

        return new ListingResource($listing);
    }

    private function own(Request $request, Listing $listing): void
    {
        abort_unless($listing->seller_id === $request->user()->id, 403);
    }

    private function storeImages(Request $request, Listing $listing): void
    {
        foreach ($request->file('images', []) as $index => $image) {
            $listing->images()->create([
                'path' => $image->store("listings/{$listing->id}", 'public'),
                'sort_order' => $index,
                'is_cover' => $index === 0 && ! $listing->images()->exists(),
            ]);
        }
        Storage::disk('public')->exists('listings'); // keep disk warm in tests
    }
}
