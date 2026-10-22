<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Listing;
use App\Models\ListingDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListingController extends Controller
{
    public function index(Request $request): View
    {
        $listings = Listing::with(['seller', 'project.developer'])
            ->filter($request->only(['search', 'city', 'unit_type', 'status']))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.listings.index', compact('listings'));
    }

    public function show(Listing $listing): View
    {
        $listing->load(['seller', 'project.developer', 'developer', 'images', 'documents.uploader', 'interests.buyer', 'deals.buyer']);

        return view('admin.listings.show', compact('listing'));
    }

    /** Verification queue action: publish / request changes / reject. */
    public function updateStatus(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:published,changes_requested,rejected,hidden'],
            'verification_notes' => ['nullable', 'string'],
        ]);
        $listing->update([
            'status' => $validated['status'],
            'verification_notes' => $validated['verification_notes'] ?? $listing->verification_notes,
            'verified_by' => $request->user()->id,
            'verified_at' => $validated['status'] === 'published' ? now() : $listing->verified_at,
            'published_at' => $validated['status'] === 'published' ? now() : $listing->published_at,
        ]);

        return back()->with('success', 'Listing '.$validated['status'].'.');
    }

    public function toggleFeatured(Listing $listing): RedirectResponse
    {
        $listing->update(['is_featured' => ! $listing->is_featured]);

        return back()->with('success', 'Featured updated.');
    }

    public function reviewDocument(Request $request, ListingDocument $document): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', 'in:verified,rejected'], 'review_notes' => ['nullable', 'string']]);
        $document->update([...$validated, 'reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);

        return back()->with('success', 'Document '.$validated['status'].'.');
    }

    public function downloadDocument(ListingDocument $document): StreamedResponse
    {
        abort_unless(Storage::disk(config('aqar.document_disk'))->exists($document->path), 404);

        return Storage::disk(config('aqar.document_disk'))->download($document->path, $document->original_name);
    }

    /** Convert a qualified interest into a Deal (case) and reserve the listing. */
    public function convertInterest(Request $request, Listing $listing): RedirectResponse
    {
        $validated = $request->validate(['interest_id' => ['required', 'exists:interests,id'], 'case_manager_id' => ['nullable', 'exists:users,id']]);
        $interest = $listing->interests()->findOrFail($validated['interest_id']);
        $deal = Deal::create([
            'listing_id' => $listing->id, 'seller_id' => $listing->seller_id, 'buyer_id' => $interest->buyer_id,
            'interest_id' => $interest->id, 'case_manager_id' => $validated['case_manager_id'] ?? $request->user()->id,
            'cash_to_seller' => $listing->paid_amount, 'total_price' => $listing->total_price,
            'commission_rate' => config('aqar.buyer_commission_rate'),
        ]);
        $interest->update(['status' => 'converted']);
        $listing->update(['status' => ListingStatus::Reserved]);

        return redirect()->route('admin.deals.show', $deal)->with('success', 'Deal created and listing reserved.');
    }

    public function destroy(Listing $listing): RedirectResponse
    {
        $listing->delete();

        return redirect()->route('admin.listings.index')->with('success', 'Listing deleted.');
    }
}
