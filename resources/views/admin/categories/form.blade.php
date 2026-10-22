@extends('layouts.admin')
@section('title', $category->exists ? 'Edit Category' : 'New Category')
@section('content')
<form method="POST" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" class="card flex max-w-2xl flex-col gap-4 p-6">
@csrf @if($category->exists) @method('PATCH') @endif
<div><label class="label">{{ __('admin.name_en') }}</label><input name="name" class="input" value="{{ old('name', $category->name) }}" required></div>
<div><label class="label">{{ __('admin.name_ar_label') }}</label><input name="name_ar" class="input" value="{{ old('name_ar', $category->name_ar) }}"></div>
<div class="grid grid-cols-2 gap-4"><div><label class="label">{{ __('admin.icon') }}</label><input name="icon" class="input" value="{{ old('icon', $category->icon) }}" placeholder="building"></div><div><label class="label">{{ __('admin.sort_order') }}</label><input name="sort_order" type="number" class="input" value="{{ old('sort_order', $category->sort_order ?? 0) }}"></div></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))> Active</label>
<button class="btn-primary w-fit">{{ __('admin.save') }}</button></form>
@endsection
