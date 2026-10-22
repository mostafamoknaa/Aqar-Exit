<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreListingRequest;
use App\Http\Resources\V1\ListingDetailResource;
use App\Http\Resources\V1\ListingResource;
use App\Models\Listing;
use App\Models\ListingDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MyListingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return ListingResource::collection(
            $request->user()->listings()->with(['images', 'category', 'project', 'developer'])->latest()->paginate(15)
        );
    }

    public function store(StoreListingRequest $request): JsonResponse
    {
        $data = $request->safe()->except(['images', 'submit']);
        $data['status'] = $request->boolean('submit') ? ListingStatus::PendingReview : ListingStatus::Draft;
        $listing = $request->user()->listings()->create($data);
        $this->storeImages($request, $listing);

        return response()->json(['data' => new ListingDetailResource($listing->load(['images', 'category', 'project', 'developer']))], 201);
    }

    public function show(Request $request, Listing $listing): ListingDetailResource
    {
        $this->own($request, $listing);

        return new ListingDetailResource($listing->load(['images', 'category', 'project', 'developer', 'documents']));
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

        return new ListingDetailResource($listing->fresh()->load(['images', 'category', 'project', 'developer']));
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
        $document->reviews()->create(['reviewer_id' => $request->user()->id, 'action' => 'submitted', 'snapshot' => $document->only(['type', 'original_name', 'status'])]);

        return response()->json(['data' => ['id' => $document->id, 'type' => $document->type, 'status' => $document->status]], 201);
    }

    /** Replace/resubmit a document (e.g. after staff rejection). Old file is deleted, review history is kept. */
    public function replaceDocument(Request $request, Listing $listing, ListingDocument $document): JsonResponse
    {
        $this->own($request, $listing);
        abort_unless($document->listing_id === $listing->id, 404);
        $request->validate(['file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240']]);
        Storage::disk(config('aqar.document_disk'))->delete($document->path);
        $document->update([
            'path' => $request->file('file')->store("listings/{$listing->id}/documents", config('aqar.document_disk')),
            'original_name' => $request->file('file')->getClientOriginalName(),
            'status' => 'pending', 'review_notes' => null, 'reviewed_by' => null, 'reviewed_at' => null,
        ]);
        $document->reviews()->create(['reviewer_id' => $request->user()->id, 'action' => 'replaced', 'snapshot' => $document->only(['type', 'original_name', 'status'])]);

        return response()->json(['data' => ['id' => $document->id, 'status' => $document->status]]);
    }

    /** Generate a temporary (5 min) signed download URL for a private document. */
    public function signedDocumentUrl(Request $request, Listing $listing, ListingDocument $document): JsonResponse
    {
        abort_unless($document->listing_id === $listing->id, 404);
        abort_unless($listing->seller_id === $request->user()->id || $request->user()->isStaff(), 403);
        $expires = now()->addMinutes(5);

        return response()->json(['data' => [
            'url' => URL::temporarySignedRoute('documents.signed-download', $expires, ['document' => $document->id]),
            'expires_at' => $expires->toISOString(),
        ]]);
    }

    /** Private document download: owner or staff only, streamed — never a public URL. */
    public function downloadDocument(Request $request, Listing $listing, ListingDocument $document): StreamedResponse
    {
        abort_unless($document->listing_id === $listing->id, 404);
        abort_unless($listing->seller_id === $request->user()->id || $request->user()->isStaff(), 403);
        abort_unless(Storage::disk(config('aqar.document_disk'))->exists($document->path), 404);

        return Storage::disk(config('aqar.document_disk'))->download($document->path, $document->original_name);
    }

    public function submit(Request $request, Listing $listing): ListingResource
    {
        $this->own($request, $listing);
        abort_unless($listing->documents()->exists(), 422, 'Upload at least the contract or payment receipts before submitting.');
        // Step 2 (submitted) first time; step 5 (documents resubmitted) after staff requested changes.
        $listing->update(['status' => $listing->status === ListingStatus::ChangesRequested ? ListingStatus::DocumentsResubmitted : ListingStatus::PendingReview]);

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
