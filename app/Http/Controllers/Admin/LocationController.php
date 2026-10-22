<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(Request $request): View
    {
        $locations = Location::with('parent')->withCount('children')
            ->when($request->input('type'), fn ($q, $v) => $q->where('type', $v))
            ->orderBy('type')->orderBy('name')->paginate(25)->withQueryString();

        return view('admin.locations.index', compact('locations'));
    }

    public function create(): View
    {
        return view('admin.locations.form', ['location' => new Location, 'parents' => Location::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        Location::create($data);

        return redirect()->route('admin.locations.index')->with('success', 'Location created.');
    }

    public function edit(Location $location): View
    {
        return view('admin.locations.form', ['location' => $location, 'parents' => Location::where('id', '!=', $location->id)->orderBy('name')->get()]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $location->update($this->validated($request));

        return back()->with('success', 'Location updated.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return back()->with('success', 'Location deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'parent_id' => ['nullable', 'exists:locations,id'],
            'type' => ['required', 'in:country,governorate,city,district'],
            'name' => ['required', 'string', 'max:100'], 'name_ar' => ['nullable', 'string', 'max:100'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
