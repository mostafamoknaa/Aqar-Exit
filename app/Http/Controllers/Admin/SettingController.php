<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'settings' => [
                'site_name' => Setting::get('site_name', 'Aqar Exit'),
                'site_name_ar' => Setting::get('site_name_ar', 'عقار إكزت'),
                'site_logo' => Setting::get('site_logo'),
                'support_email' => Setting::get('support_email', 'info@aqarexit.test'),
                'support_phone' => Setting::get('support_phone', ''),
                'buyer_commission_rate' => Setting::buyerCommissionRate(),
                'default_cancellation_percent' => Setting::get('default_cancellation_percent', config('aqar.default_cancellation_percent')),
                'default_locale' => Setting::get('default_locale', 'en'),
                'registration_enabled' => Setting::get('registration_enabled', true),
                'silent_listings_enabled' => Setting::get('silent_listings_enabled', true),
            ],
        ]);
    }

    public function update(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->can('settings.manage'), 403);
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'site_name_ar' => ['nullable', 'string', 'max:100'],
            'support_email' => ['nullable', 'email', 'max:150'],
            'support_phone' => ['nullable', 'string', 'max:30'],
            'buyer_commission_rate' => ['required', 'numeric', 'min:0', 'max:0.2'],
            'default_cancellation_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'default_locale' => ['required', 'in:en,ar'],
            'site_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $oldLogo = Setting::get('site_logo');
        if ($request->boolean('remove_logo') && $oldLogo) {
            Storage::disk('public')->delete($oldLogo);
            Setting::put('site_logo', '', 'file', 'branding');
        }
        if ($request->hasFile('site_logo')) {
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }
            Setting::put('site_logo', $request->file('site_logo')->store('branding', 'public'), 'file', 'branding');
        }

        Setting::put('site_name', $validated['site_name'], 'string', 'branding');
        Setting::put('site_name_ar', $validated['site_name_ar'] ?? '', 'string', 'branding');
        Setting::put('support_email', $validated['support_email'] ?? '', 'string', 'general');
        Setting::put('support_phone', $validated['support_phone'] ?? '', 'string', 'general');
        Setting::put('buyer_commission_rate', $validated['buyer_commission_rate'], 'float', 'financial');
        Setting::put('default_cancellation_percent', $validated['default_cancellation_percent'], 'float', 'financial');
        Setting::put('default_locale', $validated['default_locale'], 'string', 'general');
        Setting::put('registration_enabled', $request->boolean('registration_enabled'), 'boolean', 'features');
        Setting::put('silent_listings_enabled', $request->boolean('silent_listings_enabled'), 'boolean', 'features');

        $audit->log('settings.updated', null, null, ['site_name' => $validated['site_name'], 'buyer_commission_rate' => $validated['buyer_commission_rate']]);

        return back()->with('success', __('admin.settings_saved'));
    }
}
