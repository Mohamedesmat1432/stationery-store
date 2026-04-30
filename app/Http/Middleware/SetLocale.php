<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->getLocale($request);

        app()->setLocale($locale);

        return $next($request);
    }

    protected function getLocale(Request $request): string
    {
        // 1. Authenticated user preference
        if ($request->user() && $request->user()->locale) {
            return $request->user()->locale;
        }

        // 2. Session preference
        if (session()->has('locale')) {
            return session()->get('locale');
        }

        // 3. Browser preference (optional)
        // return $request->getPreferredLanguage(['en', 'ar']) ?: config('app.locale');

        // 4. Default config
        return config('app.locale', 'en');
    }
}
