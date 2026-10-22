<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'ar'];
        $locale = $request->user()?->locale
            ?? session('locale')
            ?? $request->header('Accept-Language')
            ?? config('app.locale', 'en');
        $locale = substr((string) $locale, 0, 2);
        app()->setLocale(in_array($locale, $supported, true) ? $locale : 'en');

        return $next($request);
    }
}
