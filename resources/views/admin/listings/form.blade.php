@extends('layouts.admin')
@section('title', $listing->exists ? 'Edit Listing #'.$listing->id : 'New Listing')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ $listing->exists ? route('admin.listings.update', $listing) : route('admin.listings.store') }}" class="card flex flex-col gap-4 p-6">
@csrf @if($listing->exists) @method('PATCH') @endif
<div class="grid gap-4 md:grid-cols-2">
    <div><label class="label">Seller *</label><select name="seller_id" class="input" required>@foreach($sellers as $s)<option value="{{ $s->id }}" @selected(old('seller_id', $listing->seller_id)==$s->id)>{{ $s->name }} · {{ $s->phone }}</option>@endforeach</select></div>
    <div><label class="label">Title *</label><input name="title" class="input" value="{{ old('title', $listing->title) }}" required></div>
    <div><label class="label">{{ __('admin.project') }}</label><select name="project_id" class="input"><option value="">—</option>@foreach($projects as $p)<option value="{{ $p->id }}" @selected(old('project_id', $listing->project_id)==$p->id)>{{ $p->name }} ({{ $p->developer->name }})</option>@endforeach</select></div>
    <div><label class="label">{{ __('admin.category') }}</label><select name="category_id" class="input"><option value="">—</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $listing->category_id)==$c->id)>{{ $c->name }}</option>@endforeach</select></div>
    <div><label class="label">Unit type *</label><select name="unit_type" class="input">@foreach(\App\Enums\UnitType::cases() as $t)<option value="{{ $t->value }}" @selected(old('unit_type', $listing->unit_type?->value)===$t->value)>{{ $t->label() }}</option>@endforeach</select></div>
    <div><label class="label">Status *</label><select name="status" class="input">@foreach(\App\Enums\ListingStatus::cases() as $s)<option value="{{ $s->value }}" @selected(old('status', $listing->status?->value ?? 'draft')===$s->value)>{{ $s->label() }}</option>@endforeach</select></div>
    <div><label class="label">{{ __('admin.governorate_label') }}</label><input name="governorate" class="input" value="{{ old('governorate', $listing->governorate) }}"></div>
    <div><label class="label">City *</label><input name="city" class="input" value="{{ old('city', $listing->city) }}" required></div>
    <div><label class="label">{{ __('admin.district') }}</label><input name="district" class="input" value="{{ old('district', $listing->district) }}"></div>
    <div><label class="label">Area m² *</label><input name="area_sqm" type="number" step="0.01" class="input" value="{{ old('area_sqm', $listing->area_sqm) }}" required></div>
    <div class="grid grid-cols-3 gap-3"><div><label class="label">{{ __('admin.bedrooms') }}</label><input name="bedrooms" type="number" class="input" value="{{ old('bedrooms', $listing->bedrooms ?? 0) }}"></div><div><label class="label">{{ __('admin.bathrooms') }}</label><input name="bathrooms" type="number" class="input" value="{{ old('bathrooms', $listing->bathrooms ?? 0) }}"></div><div><label class="label">{{ __('admin.floor') }}</label><input name="floor" type="number" class="input" value="{{ old('floor', $listing->floor) }}"></div></div>
    <div class="grid grid-cols-2 gap-3"><div><label class="label">{{ __('admin.latitude') }}</label><input name="latitude" type="number" step="any" class="input" value="{{ old('latitude', $listing->latitude) }}"></div><div><label class="label">{{ __('admin.longitude') }}</label><input name="longitude" type="number" step="any" class="input" value="{{ old('longitude', $listing->longitude) }}"></div></div>
</div>
<div><label class="label">{{ __('admin.description') }}</label><textarea name="description" rows="3" class="input">{{ old('description', $listing->description) }}</textarea></div>
<div class="grid gap-4 md:grid-cols-3">
    <div><label class="label">Total price (old contract) *</label><input name="total_price" type="number" step="0.01" class="input" value="{{ old('total_price', $listing->total_price) }}" required></div>
    <div><label class="label">Paid amount (cash to seller) *</label><input name="paid_amount" type="number" step="0.01" class="input" value="{{ old('paid_amount', $listing->paid_amount) }}" required></div>
    <div><label class="label">{{ __('admin.market_estimate') }}</label><input name="market_price" type="number" step="0.01" class="input" value="{{ old('market_price', $listing->market_price) }}"></div>
    <div><label class="label">{{ __('admin.installment_amount') }}</label><input name="installment_amount" type="number" step="0.01" class="input" value="{{ old('installment_amount', $listing->installment_amount) }}"></div>
    <div><label class="label">{{ __('admin.installments_remaining') }}</label><input name="installments_remaining" type="number" class="input" value="{{ old('installments_remaining', $listing->installments_remaining) }}"></div>
    <div><label class="label">{{ __('admin.images_add') }}</label><input name="images[]" type="file" multiple accept="image/*" class="input"></div>
</div>
<div class="flex gap-6"><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $listing->is_featured))> Featured</label><label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_silent" value="1" @checked(old('is_silent', $listing->is_silent))> Silent exit</label></div>
<div class="flex gap-2"><button class="btn-primary">{{ __('admin.save') }}</button><a href="{{ route('admin.listings.index') }}" class="btn-secondary">{{ __('admin.cancel') }}</a></div>
</form>
@endsection
