@extends('layouts.admin')
@section('title', $project->exists ? 'Edit Project' : 'New Project')
@section('content')
<form method="POST" action="{{ $project->exists ? route('admin.projects.update', $project) : route('admin.projects.store') }}" class="card flex max-w-2xl flex-col gap-4 p-6">
@csrf @if($project->exists) @method('PATCH') @endif
<div><label class="label">Developer</label><select name="developer_id" class="input">@foreach($developers as $d)<option value="{{ $d->id }}" @selected(old('developer_id', $project->developer_id)==$d->id)>{{ $d->name }}</option>@endforeach</select></div>
<div><label class="label">Name (EN)</label><input name="name" class="input" value="{{ old('name', $project->name) }}" required></div>
<div><label class="label">Name (AR)</label><input name="name_ar" class="input" value="{{ old('name_ar', $project->name_ar) }}"></div>
<div class="grid grid-cols-2 gap-4"><div><label class="label">City</label><input name="city" class="input" value="{{ old('city', $project->city) }}" required></div><div><label class="label">District</label><input name="district" class="input" value="{{ old('district', $project->district) }}"></div></div>
<div><label class="label">Description</label><textarea name="description" class="input" rows="3">{{ old('description', $project->description) }}</textarea></div>
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $project->is_active))> Active</label>
<button class="btn-primary w-fit">Save</button>
</form>
@endsection
