@extends('layouts.admin')
@section('title', $location->exists ? 'Edit Location' : 'New Location')
@section('content')
<form method="POST" action="{{ $location->exists ? route('admin.locations.update', $location) : route('admin.locations.store') }}" class="card flex max-w-2xl flex-col gap-4 p-6">
@csrf @if($location->exists) @method('PATCH') @endif
<div><label class="label">{{ __('admin.parent') }}</label><select name="parent_id" class="input"><option value="">— None —</option>@foreach($parents as $p)<option value="{{ $p->id }}" @selected(old('parent_id', $location->parent_id)==$p->id)>{{ $p->name }} ({{ $p->type }})</option>@endforeach</select></div>
<div><label class="label">{{ __('admin.type') }}</label><select name="type" class="input">@foreach(['country','governorate','city','district'] as $t)<option @selected(old('type', $location->type)===$t)>{{ $t }}</option>@endforeach</select></div>
<div><label class="label">{{ __('admin.name_en') }}</label><input name="name" class="input" value="{{ old('name', $location->name) }}" required></div>
<div><label class="label">{{ __('admin.name_ar_label') }}</label><input name="name_ar" class="input" value="{{ old('name_ar', $location->name_ar) }}"></div>
<div class="grid grid-cols-2 gap-4"><div><label class="label">{{ __('admin.latitude') }}</label><input name="latitude" type="number" step="any" class="input" value="{{ old('latitude', $location->latitude) }}"></div><div><label class="label">{{ __('admin.longitude') }}</label><input name="longitude" type="number" step="any" class="input" value="{{ old('longitude', $location->longitude) }}"></div></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $location->is_active))> Active</label>
<button class="btn-primary w-fit">{{ __('admin.save') }}</button></form>
@endsection
