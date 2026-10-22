<!DOCTYPE html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login — {{ \App\Models\Setting::siteName() }}</title>@vite(['resources/css/app.css', 'resources/js/app.js'])</head>
<body class="flex min-h-screen items-center justify-center bg-ink-900 p-4 font-sans">
<div class="card w-full max-w-md p-8">
    <div class="mb-6 flex items-center gap-3">
        @if (\App\Models\Setting::logoUrl())<img src="{{ \App\Models\Setting::logoUrl() }}" class="h-12 w-12 rounded-xl object-contain" alt="">
        @else<div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-600 text-white"><x-icon name="building" class="h-7 w-7" /></div>@endif
        <div><h1 class="text-xl font-bold">{{ \App\Models\Setting::siteName() }}</h1><p class="text-sm text-ink-500">{{ __('admin.staff_only') }}</p></div>
    </div>
    @if ($errors->any()) <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div> @endif
    <form method="POST" action="{{ route('admin.login.store') }}" class="flex flex-col gap-4">
        @csrf
        <div><label class="label" for="email">{{ __('admin.email') }}</label><input id="email" name="email" type="email" class="input" value="{{ old('email') }}" required autofocus></div>
        <div><label class="label" for="password">{{ __('admin.password') }}</label><input id="password" name="password" type="password" class="input" required></div>
        <label class="flex items-center gap-2 text-sm text-ink-700"><input type="checkbox" name="remember" class="rounded"> Remember me</label>
        <button class="btn-primary w-full">{{ __('admin.login') }}</button>
    </form>
    <p class="mt-6 rounded-lg bg-gray-50 p-3 text-xs text-ink-500">Demo (after seed): <code>admin@aqarexit.test</code> / <code>password</code></p>
</div>
</body></html>
