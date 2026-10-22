<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — Aqar Exit Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans text-ink-900 antialiased">
<div class="flex min-h-screen">
    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 start-0 z-40 hidden w-64 bg-ink-900 lg:block">
        <div class="flex h-16 items-center gap-3 border-b border-white/10 px-5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-lg font-bold text-white">ع</div>
            <div>
                <p class="text-sm font-bold text-white">Aqar Exit</p>
                <p class="text-xs text-gray-400">Admin Dashboard</p>
            </div>
        </div>
        <nav class="flex flex-col gap-1 p-4">
            @php $link = fn($route, $label) => '<a href="'.route($route).'" class="nav-link '.(request()->routeIs($route) || request()->routeIs($route.'.*') ? 'nav-link-active' : '').'">'.$label.'</a>'; @endphp
            {!! $link('admin.dashboard', '📊 Dashboard') !!}
            {!! $link('admin.listings.index', '🏠 Listings / Verification') !!}
            {!! $link('admin.evaluations.index', '🧮 Exit Evaluations') !!}
            {!! $link('admin.interests.index', '🙋 Buyer Interests') !!}
            {!! $link('admin.deals.index', '🤝 Deals / Cases') !!}
            {!! $link('admin.users.index', '👥 Users') !!}
            {!! $link('admin.developers.index', '🏗️ Developers') !!}
            {!! $link('admin.projects.index', '🏙️ Projects') !!}
        </nav>
        <div class="absolute inset-x-4 bottom-4 rounded-lg bg-white/5 p-3 text-xs text-gray-400">
            Seller commission: <span class="font-semibold text-white">0%</span><br>
            Buyer commission: <span class="font-semibold text-white">{{ config('aqar.buyer_commission_rate') * 100 }}%</span> on completion
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col lg:ps-64">
        <header class="sticky top-0 z-30 flex h-16 items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button class="rounded-lg p-2 hover:bg-gray-100 lg:hidden" onclick="document.getElementById('mobile-nav').classList.toggle('hidden')">☰</button>
                <h1 class="truncate text-base font-bold sm:text-lg">@yield('title', 'Dashboard')</h1>
            </div>
            <div class="flex items-center gap-3">
                <span class="hidden text-sm text-ink-500 sm:block">{{ auth()->user()->name }} · {{ auth()->user()->role->label() }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf <button class="btn-secondary">Logout</button></form>
            </div>
        </header>

        <div id="mobile-nav" class="hidden border-b border-gray-200 bg-ink-900 p-3 lg:hidden">
            <nav class="flex flex-col gap-1">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="nav-link" href="{{ route('admin.listings.index') }}">Listings</a>
                <a class="nav-link" href="{{ route('admin.deals.index') }}">Deals</a>
                <a class="nav-link" href="{{ route('admin.users.index') }}">Users</a>
                <a class="nav-link" href="{{ route('admin.evaluations.index') }}">Evaluations</a>
                <a class="nav-link" href="{{ route('admin.interests.index') }}">Interests</a>
                <a class="nav-link" href="{{ route('admin.developers.index') }}">Developers</a>
                <a class="nav-link" href="{{ route('admin.projects.index') }}">Projects</a>
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
