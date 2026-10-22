<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\ListingDocument;
use App\Models\Project;
use App\Models\PropertyCategory;
use App\Models\Setting;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\FinancialsService;
use App\Services\ListingTransitionService;
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

    public function create(): View
    {
        return view('admin.listings.form', $this->formData(new Listing));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedListing($request);
        $listing = Listing::create($data);
        $this->storeImages($request, $listing);
        app(AuditLogger::class)->log('listing.created_by_admin', $listing, null, ['seller_id' => $listing->seller_id]);

        return redirect()->route('admin.listings.show', $listing)->with('success', 'Listing created.');
    }

    public function edit(Listing $listing): View
    {
        return view('admin.listings.form', $this->formData($listing));
    }

    public function update(Request $request, Listing $listing): RedirectResponse
    {
        $listing->update($this->validatedListing($request, $listing));
        $this->storeImages($request, $listing);
        app(AuditLogger::class)->log('listing.updated_by_admin', $listing, null, []);

        return redirect()->route('admin.listings.show', $listing)->with('success', 'Listing updated.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_unless($request->user()->can('reports.export'), 403);
        $listings = Listing::with(['seller', 'project'])->filter($request->only(['search', 'city', 'unit_type', 'status']))->latest()->limit(5000)->get();

        return response()->streamDownload(function () use ($listings) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['reference', 'title', 'seller', 'phone', 'city', 'district', 'unit_type', 'area_sqm', 'total_price', 'paid_amount', 'remaining_amount', 'market_price', 'financial_status', 'status', 'created_at']);
            foreach ($listings as $l) {
                fputcsv($out, [$l->reference, $l->title, $l->seller->name, $l->seller->phone, $l->city, $l->district, $l->unit_type?->value, $l->area_sqm, $l->total_price, $l->paid_amount, $l->remaining_amount, $l->market_price, $l->financial_status, $l->status->value, $l->created_at]);
            }
            fclose($out);
        }, 'listings-'.now()->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv']);
    }

    private function formData(Listing $listing): array
    {
        return [
            'listing' => $listing,
            'sellers' => User::where('role', 'user')->orderBy('name')->get(),
            'developers' => Developer::orderBy('name')->get(),
            'projects' => Project::with('developer')->orderBy('name')->get(),
            'categories' => PropertyCategory::where('is_active', true)->orderBy('sort_order')->get(),
        ];
    }

    private function validatedListing(Request $request, ?Listing $listing = null): array
    {
        return $request->validate([
            'seller_id' => ['required', 'exists:users,id'],
            'developer_id' => ['nullable', 'exists:developers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'category_id' => ['nullable', 'exists:property_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit_type' => ['required', 'in:'.implode(',', array_column(UnitType::cases(), 'value'))],
            'country' => ['nullable', 'string', 'max:100'],
            'governorate' => ['nullable', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'area_sqm' => ['required', 'numeric', 'min:1'],
            'bedrooms' => ['nullable', 'integer', 'min:0'],
            'bathrooms' => ['nullable', 'integer', 'min:0'],
            'floor' => ['nullable', 'integer', 'min:0'],
            'total_price' => ['required', 'numeric', 'min:1'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'lte:total_price'],
            'market_price' => ['nullable', 'numeric', 'min:0'],
            'installment_amount' => ['nullable', 'numeric', 'min:0'],
            'installments_remaining' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'in:'.implode(',', array_column(ListingStatus::cases(), 'value'))],
            'is_featured' => ['nullable', 'boolean'],
            'is_silent' => ['nullable', 'boolean'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['image', 'max:5120'],
        ]) + ['is_featured' => $request->boolean('is_featured'), 'is_silent' => $request->boolean('is_silent')];
    }

    private function storeImages(Request $request, Listing $listing): void
    {
        foreach ($request->file('images', []) as $i => $image) {
            $listing->images()->create(['path' => $image->store("listings/{$listing->id}", 'public'), 'sort_order' => $listing->images()->count() + $i, 'is_cover' => ! $listing->images()->exists()]);
        }
    }

    /** Map of geocoded listings (Leaflet/OpenStreetMap — no API key required). */
    public function map(): View
    {
        $listings = Listing::with(['seller', 'project'])
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->latest()->limit(500)->get();

        return view('admin.listings.map', compact('listings'));
    }

    public function show(Listing $listing): View
    {
        $listing->load(['seller', 'project.developer', 'developer', 'images', 'documents.uploader', 'documents.reviews.reviewer', 'interests.buyer', 'interests.messages.sender', 'deals.buyer']);

        return view('admin.listings.show', compact('listing'));
    }

    /** Verification queue action — only valid workflow transitions are accepted. */
    public function updateStatus(Request $request, Listing $listing, ListingTransitionService $transitions): RedirectResponse
    {
        abort_unless($request->user()->can('listings.moderate'), 403);
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_column(ListingStatus::cases(), 'value'))],
            'verification_notes' => ['nullable', 'string'],
        ]);
        $transitions->transition($listing, ListingStatus::from($validated['status']), $request->user(), $validated['verification_notes'] ?? null);

        return back()->with('success', 'Listing '.$validated['status'].'.');
    }

    /** Record independently verified financials; mismatch flags discrepancy, never overwrites reported figures. */
    public function verifyFinancials(Request $request, Listing $listing, FinancialsService $financials): RedirectResponse
    {
        abort_unless($request->user()->can('financials.verify'), 403);
        $validated = $request->validate([
            'verified_total_price' => ['required', 'numeric', 'min:0'],
            'verified_paid_amount' => ['required', 'numeric', 'min:0'],
            'financial_notes' => ['nullable', 'string'],
        ]);
        $financials->verify($listing, (string) $validated['verified_total_price'], (string) $validated['verified_paid_amount'], $request->user(), $validated['financial_notes'] ?? null);

        return back()->with('success', 'Financial verification recorded: '.$listing->fresh()->financial_status);
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
        $document->reviews()->create(['reviewer_id' => $request->user()->id, 'action' => $validated['status'], 'notes' => $validated['review_notes'] ?? null, 'snapshot' => $document->only(['type', 'original_name', 'status'])]);
        app(AuditLogger::class)->log('document.reviewed', $document, null, $validated);

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
            'commission_rate' => Setting::buyerCommissionRate(),
        ]);
        $deal->events()->create(['actor_id' => $request->user()->id, 'from_stage' => null, 'to_stage' => $deal->stage->value, 'reason' => 'Case created from qualified interest.']);
        app(AuditLogger::class)->log('deal.created', $deal, null, ['listing_id' => $listing->id, 'buyer_id' => $interest->buyer_id]);
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
