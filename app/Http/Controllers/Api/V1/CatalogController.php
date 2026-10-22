<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UnitType;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\DeveloperResource;
use App\Http\Resources\V1\ProjectResource;
use App\Models\Developer;
use App\Models\Listing;
use App\Models\Project;
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

    /** Filter metadata for the mobile search screen. */
    public function filters(): JsonResponse
    {
        return response()->json(['data' => [
            'cities' => Listing::published()->distinct()->pluck('city'),
            'unit_types' => collect(UnitType::cases())->map(fn ($t) => ['key' => $t->value, 'label' => $t->label()]),
        ]]);
    }
}
