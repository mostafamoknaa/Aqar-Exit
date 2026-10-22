<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DeveloperResource;
use App\Http\Resources\V1\ProjectResource;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\Location;
use App\Models\Project;
use App\Models\PropertyCategory;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends Controller
{
    public function developers(): AnonymousResourceCollection
    {
        return DeveloperResource::collection(Developer::where('is_active', true)->withCount('projects')->orderBy('name')->get());
    }

    public function projects(): AnonymousResourceCollection
    {
        return ProjectResource::collection(
            Project::where('is_active', true)->with('developer')->withCount(['listings' => fn ($q) => $q->published()])
                ->when(request('city'), fn ($q, $v) => $q->where('city', $v))
                ->when(request('developer_id'), fn ($q, $v) => $q->where('developer_id', $v))
                ->orderBy('name')->paginate(20)
        );
    }

    public function categories(): JsonResponse
    {
        return response()->json(['data' => PropertyCategory::where('is_active', true)->orderBy('sort_order')->get()->map(fn ($c) => [
            'id' => $c->id, 'name' => $c->name, 'name_ar' => $c->name_ar, 'localized_name' => $c->localizedName(), 'slug' => $c->slug, 'icon' => $c->icon,
        ])]);
    }

    public function locations(): JsonResponse
    {
        return response()->json(['data' => Location::where('is_active', true)->with('children')->whereNull('parent_id')->orderBy('name')->get()]);
    }

    /** Public branding for the mobile splash / about screens — managed from Admin → Settings. */
    public function branding(): JsonResponse
    {
        return response()->json(['data' => [
            'site_name' => Setting::siteName(),
            'site_name_ar' => Setting::get('site_name_ar'),
            'logo_url' => Setting::logoUrl(),
            'support_email' => Setting::get('support_email'),
            'support_phone' => Setting::get('support_phone'),
            'default_locale' => Setting::get('default_locale', 'en'),
            'supported_locales' => ['en', 'ar'],
            'buyer_commission_rate' => Setting::buyerCommissionRate(),
            'seller_commission_rate' => 0,
        ]]);
    }

    /** Filter metadata for the mobile search screen. */
    public function filters(): JsonResponse
    {
        return response()->json(['data' => [
            'cities' => Listing::published()->distinct()->pluck('city'),
            'unit_types' => collect(UnitType::cases())->map(fn ($t) => ['key' => $t->value, 'label' => $t->label()]),
        ]]);
    }
}
