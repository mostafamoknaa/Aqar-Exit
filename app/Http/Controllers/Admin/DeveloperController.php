<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DeveloperController extends Controller
{
    public function index(): View
    {
        return view('admin.developers.index', ['developers' => Developer::withCount(['projects', 'listings'])->latest()->paginate(15)]);
    }

    public function create(): View
    {
        return view('admin.developers.form', ['developer' => new Developer]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        Developer::create($data);

        return redirect()->route('admin.developers.index')->with('success', 'Developer created.');
    }

    public function edit(Developer $developer): View
    {
        return view('admin.developers.form', compact('developer'));
    }

    public function update(Request $request, Developer $developer): RedirectResponse
    {
        $developer->update($this->validated($request));

        return back()->with('success', 'Developer updated.');
    }

    public function destroy(Developer $developer): RedirectResponse
    {
        $developer->delete();

        return back()->with('success', 'Developer deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'], 'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'], 'assignment_notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
