@extends('layouts.admin')
@section('title', $developer->exists ? 'Edit Developer' : 'New Developer')
@section('content')
<form method="POST" action="{{ $developer->exists ? route('admin.developers.update', $developer) : route('admin.developers.store') }}" class="card flex max-w-2xl flex-col gap-4 p-6">
@csrf @if($developer->exists) @method('PATCH') @endif
<div><label class="label">{{ __('admin.name_en') }}</label><input name="name" class="input" value="{{ old('name', $developer->name) }}" required></div>
<div><label class="label">{{ __('admin.name_ar_label') }}</label><input name="name_ar" class="input" value="{{ old('name_ar', $developer->name_ar) }}"></div>
<div><label class="label">{{ __('admin.description') }}</label><textarea name="description" class="input" rows="3">{{ old('description', $developer->description) }}</textarea></div>
<div><label class="label">Assignment (تنازل) notes — fees, steps at this developer</label><textarea name="assignment_notes" class="input" rows="3">{{ old('assignment_notes', $developer->assignment_notes) }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $developer->is_active))> Active</label>
<button class="btn-primary w-fit">{{ __('admin.save') }}</button>
</form>
@endsection
