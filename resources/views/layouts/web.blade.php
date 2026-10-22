<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', \App\Models\Setting::siteName()) — {{ \App\Models\Setting::siteName() }}</title>
    <meta name="description" content="@yield('meta_description', 'Sell your installment unit with no overprice and recover what you paid, or buy at the old contract price.')">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-white font-sans text-ink-900 antialiased">
@php $siteName = app()->getLocale() === 'ar' ? (\App\Models\Setting::get('site_name_ar') ?: \App\Models\Setting::siteName()) : \App\Models\Setting::siteName(); $logo = \App\Models\Setting::logoUrl(); @endphp
<header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6">
        <a href="{{ route('web.home') }}" class="flex items-center gap-2.5">
            @if($logo)<img src="{{ $logo }}" class="h-10 w-10 rounded-lg object-contain" alt="{{ $siteName }}">@else<span class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-600 text-white"><x-icon name="building" class="h-6 w-6" /></span>@endif
            <span class="text-lg font-bold">{{ $siteName }}</span>
        </a>
        <nav class="hidden items-center gap-6 text-sm font-medium md:flex">
            <a href="{{ route('web.listings.index') }}" class="hover:text-brand-700">Browse Units</a>
            <a href="{{ route('web.evaluate') }}" class="hover:text-brand-700">Evaluate My Contract</a>
            <a href="{{ route('web.listings.index') }}#how" class="hover:text-brand-700">How It Works</a>
        </nav>
        <div class="flex items-center gap-2">
            <div class="flex overflow-hidden rounded-lg border border-gray-200 text-xs font-semibold">
                <a href="{{ route('locale.switch', 'en') }}" class="px-2 py-1.5 {{ app()->getLocale()==='en' ? 'bg-brand-600 text-white' : '' }}">EN</a>
                <a href="{{ route('locale.switch', 'ar') }}" class="px-2 py-1.5 {{ app()->getLocale()==='ar' ? 'bg-brand-600 text-white' : '' }}">عربي</a>
            </div>
            @auth <a href="{{ auth()->user()->isStaff() ? route('admin.dashboard') : route('web.listings.index') }}" class="btn-primary hidden sm:inline-flex">My Account</a>
            @else <a href="{{ route('admin.login') }}" class="btn-secondary hidden sm:inline-flex">Login</a> @endauth
            <a href="{{ route('web.evaluate') }}" class="btn-primary">Sell My Unit</a>
        </div>
    </div>
</header>

@if (session('success'))<div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6"><div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div></div>@endif
@if ($errors->any())<div class="mx-auto mt-4 max-w-7xl px-4 sm:px-6"><div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div></div>@endif

<main>@yield('content')</main>

<footer class="mt-16 border-t border-gray-100 bg-ink-900 text-gray-300">
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3">
        <div><p class="mb-2 flex items-center gap-2 text-lg font-bold text-white">@if($logo)<img src="{{ $logo }}" class="h-8 w-8 rounded bg-white object-contain p-0.5" alt="">@endif {{ $siteName }}</p>
            <p class="text-sm text-gray-400">The seller recovers what he paid — no overprice. The buyer takes the old contract price. Verified numbers, guided assignment, step by step.</p></div>
        <div><p class="mb-3 font-bold text-white">Platform</p><ul class="flex flex-col gap-2 text-sm"><li><a href="{{ route('web.listings.index') }}">Browse units</a></li><li><a href="{{ route('web.evaluate') }}">Free exit evaluation</a></li><li><a href="{{ route('admin.login') }}">Staff login</a></li></ul></div>
        <div><p class="mb-3 font-bold text-white">Contact</p><p class="text-sm">{{ \App\Models\Setting::get('support_email') }}<br>{{ \App\Models\Setting::get('support_phone') }}</p>
            <p class="mt-3 text-xs text-gray-500">Seller commission 0% · Buyer {{ \App\Models\Setting::buyerCommissionRate()*100 }}% on completion only</p></div>
    </div>
    <div class="border-t border-white/10 py-4 text-center text-xs text-gray-500">© {{ date('Y') }} {{ $siteName }}</div>
</footer>
@stack('scripts')
</body>
</html>
