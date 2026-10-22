<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('admin.dashboard')) — {{ \App\Models\Setting::siteName() }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-ink-900 antialiased">
@php
    $siteName = app()->getLocale() === 'ar' ? (\App\Models\Setting::get('site_name_ar') ?: \App\Models\Setting::siteName()) : \App\Models\Setting::siteName();
    $logoUrl = \App\Models\Setting::logoUrl();
    $nav = [
        ['admin.dashboard', 'home', __('admin.dashboard')],
        ['admin.listings.index', 'building', __('admin.listings')],
        ['admin.evaluations.index', 'calculator', __('admin.evaluations')],
        ['admin.interests.index', 'users', __('admin.interests')],
        ['admin.deals.index', 'handshake', __('admin.deals')],
        ['admin.users.index', 'users', __('admin.users')],
        ['admin.developers.index', 'building-office', __('admin.developers')],
        ['admin.projects.index', 'map-pin', __('admin.projects')],
        ['admin.categories.index', 'tag', __('admin.categories')],
        ['admin.locations.index', 'globe', __('admin.locations')],
        ['admin.reports.index', 'chart', __('admin.reports')],
    ];
    if (auth()->user()->can('audit.view')) {
        $nav[] = ['admin.audit-logs.index', 'shield', __('admin.audit_logs')];
    }
    if (auth()->user()->can('permissions.manage')) {
        $nav[] = ['admin.permissions.index', 'shield', __('admin.permissions')];
    }
    if (auth()->user()->can('settings.view')) {
        $nav[] = ['admin.settings.edit', 'cog', __('admin.settings')];
    }
@endphp
<div class="flex min-h-screen">
    <aside class="fixed inset-y-0 start-0 z-40 hidden w-64 overflow-y-auto bg-ink-900 lg:block">
        <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            @if ($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $siteName }}" class="h-10 w-10 rounded-lg bg-white object-contain p-1">
            @else
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-600 text-white"><x-icon name="building" class="h-6 w-6" /></div>
            @endif
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-white">{{ $siteName }}</p>
                <p class="text-xs text-gray-400">{{ __('admin.admin_dashboard') }}</p>
            </div>
        </div>
        <nav class="flex flex-col gap-1 p-4">
            @foreach ($nav as [$route, $icon, $label])
                <a href="{{ route($route) }}" class="nav-link {{ request()->routeIs($route) || request()->routeIs(str_replace('.index', '.*', $route)) ? 'nav-link-active' : '' }}">
                    <x-icon name="{{ $icon }}" /> <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
        <div class="mx-4 mb-4 rounded-lg bg-white/5 p-3 text-xs text-gray-400">
            Seller commission: <span class="font-semibold text-white">0%</span><br>
            Buyer commission: <span class="font-semibold text-white">{{ \App\Models\Setting::buyerCommissionRate() * 100 }}%</span> on completion
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col lg:ps-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button class="rounded-lg p-2 hover:bg-gray-100 lg:hidden" aria-label="Menu" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')"><x-icon name="menu" /></button>
                @if ($logoUrl)<img src="{{ $logoUrl }}" class="h-8 w-8 rounded object-contain lg:hidden" alt="">@endif
                <h1 class="truncate text-base font-bold sm:text-lg">@yield('title', __('admin.dashboard'))</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-3">
                <div class="flex overflow-hidden rounded-lg border border-gray-300 text-sm font-semibold">
                    <a href="{{ route('locale.switch', 'en') }}" class="px-2.5 py-1.5 {{ app()->getLocale() === 'en' ? 'bg-brand-600 text-white' : 'bg-white text-ink-700' }}">EN</a>
                    <a href="{{ route('locale.switch', 'ar') }}" class="px-2.5 py-1.5 {{ app()->getLocale() === 'ar' ? 'bg-brand-600 text-white' : 'bg-white text-ink-700' }}">عربي</a>
                </div>
                <span class="hidden text-sm text-ink-500 xl:block">{{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf
                    <button class="btn-secondary"><x-icon name="logout" class="h-4 w-4" /> <span class="hidden sm:inline">{{ __('admin.logout') }}</span></button>
                </form>
            </div>
        </header>

        <div id="mobile-nav" class="hidden border-b border-gray-200 bg-ink-900 p-3 lg:hidden">
            <nav class="flex flex-col gap-1">
                @foreach ($nav as [$route, $icon, $label])
                    <a class="nav-link" href="{{ route($route) }}"><x-icon name="{{ $icon }}" /> {{ $label }}</a>
                @endforeach
            </nav>
        </div>

        <main class="flex flex-col gap-6 p-4 sm:p-6">
            @if (session('success')) <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div> @endif
            @if ($errors->any()) <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">@foreach ($errors->all() as $e) <p>{{ $e }}</p> @endforeach</div> @endif
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
