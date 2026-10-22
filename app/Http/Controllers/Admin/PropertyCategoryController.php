<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PropertyCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', ['categories' => PropertyCategory::withCount('listings')->orderBy('sort_order')->paginate(20)]);
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new PropertyCategory]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']).'-'.Str::random(4);
        PropertyCategory::create($data);

        return redirect()->route('admin.categories.index')->with('success', 'Category created.');
    }

    public function edit(PropertyCategory $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, PropertyCategory $category): RedirectResponse
    {
        $category->update($this->validated($request));

        return back()->with('success', 'Category updated.');
    }

    public function destroy(PropertyCategory $category): RedirectResponse
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'], 'name_ar' => ['nullable', 'string', 'max:100'],
            'icon' => ['nullable', 'string', 'max:50'], 'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active', true)];
    }
}
