@extends('layouts.admin')
@section('title', __('admin.settings'))
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}" class="grid gap-6 xl:grid-cols-2">
    @csrf @method('PUT')

    <div class="card flex flex-col gap-4 p-6">
        <h2 class="flex items-center gap-2 font-bold"><x-icon name="building" class="text-brand-600" /> Branding</h2>
        <div><label class="label" for="site_name">{{ __('admin.site_name') }} *</label><input id="site_name" name="site_name" class="input" value="{{ old('site_name', $settings['site_name']) }}" required></div>
        <div><label class="label" for="site_name_ar">{{ __('admin.site_name_ar') }}</label><input id="site_name_ar" name="site_name_ar" class="input" dir="rtl" value="{{ old('site_name_ar', $settings['site_name_ar']) }}"></div>
        <div>
            <label class="label" for="site_logo">{{ __('admin.site_logo') }}</label>
            @if ($settings['site_logo'])
                <div class="mb-3 flex items-center gap-4 rounded-lg bg-gray-50 p-3">
                    <img src="{{ \App\Models\Setting::logoUrl() }}" class="h-16 w-16 rounded-lg bg-white object-contain p-1" alt="">
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remove_logo" value="1"> {{ __('admin.remove_logo') }}</label>
                </div>
            @endif
            <input id="site_logo" name="site_logo" type="file" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="input">
            <p class="mt-1 text-xs text-ink-500">PNG/JPG/WebP/SVG, max 2MB. Shown in dashboard sidebar, login, and mobile app via <code>GET /api/v1/branding</code>.</p>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="label">{{ __('admin.support_email') }}</label><input name="support_email" type="email" class="input" value="{{ old('support_email', $settings['support_email']) }}"></div>
            <div><label class="label">{{ __('admin.support_phone') }}</label><input name="support_phone" class="input" value="{{ old('support_phone', $settings['support_phone']) }}"></div>
        </div>
    </div>

    <div class="flex flex-col gap-6">
        <div class="card flex flex-col gap-4 p-6">
            <h2 class="flex items-center gap-2 font-bold"><x-icon name="calculator" class="text-brand-600" /> Financial Defaults</h2>
            <div><label class="label">{{ __('admin.buyer_commission') }}</label><input name="buyer_commission_rate" type="number" step="0.0001" min="0" max="0.2" class="input" value="{{ old('buyer_commission_rate', $settings['buyer_commission_rate']) }}"></div>
            <div><label class="label">{{ __('admin.cancellation_percent') }}</label><input name="default_cancellation_percent" type="number" step="0.01" min="0" max="100" class="input" value="{{ old('default_cancellation_percent', $settings['default_cancellation_percent']) }}"></div>
            <p class="text-xs text-ink-500">Changing commission affects <strong>new</strong> deals only — existing deals keep their snapshot rate.</p>
        </div>
        <div class="card flex flex-col gap-4 p-6">
            <h2 class="flex items-center gap-2 font-bold"><x-icon name="cog" class="text-brand-600" /> General & Features</h2>
            <div><label class="label">{{ __('admin.default_locale') }}</label><select name="default_locale" class="input"><option value="en" @selected($settings['default_locale']==='en')>English</option><option value="ar" @selected($settings['default_locale']==='ar')>العربية</option></select></div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="registration_enabled" value="1" @checked($settings['registration_enabled'])> {{ __('admin.registration_enabled') }}</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="silent_listings_enabled" value="1" @checked($settings['silent_listings_enabled'])> {{ __('admin.silent_listings_enabled') }}</label>
        </div>
        <button class="btn-primary w-fit"><x-icon name="check-badge" class="h-5 w-5" /> {{ __('admin.save') }}</button>
    </div>
</form>
@endsection
