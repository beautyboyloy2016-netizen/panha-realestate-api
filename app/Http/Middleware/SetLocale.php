<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get available locales from config
        $availableLocales = config('app.available_locales', ['en', 'km']);

        // Priority: URL parameter > Session > User preference > Browser > Default
        $locale = $request->get('lang')
            ?? Session::get('locale')
            ?? $request->user()?->locale
            ?? $this->getBrowserLocale($request, $availableLocales)
            ?? config('app.locale', 'en');

        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            $locale = config('app.locale', 'en');
        }

        // Set application locale
        App::setLocale($locale);

        // Store in session for persistence
        Session::put('locale', $locale);

        return $next($request);
    }

    /**
     * Get locale from browser accept-language header
     */
    private function getBrowserLocale(Request $request, array $availableLocales): ?string
    {
        $acceptLanguage = $request->server('HTTP_ACCEPT_LANGUAGE');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse accept-language header
        $languages = explode(',', $acceptLanguage);

        foreach ($languages as $language) {
            $locale = strtolower(trim(explode(';', $language)[0]));

            // Check for exact match
            if (in_array($locale, $availableLocales)) {
                return $locale;
            }

            // Check for language code match (e.g., 'en-US' -> 'en')
            $shortLocale = explode('-', $locale)[0];
            if (in_array($shortLocale, $availableLocales)) {
                return $shortLocale;
            }
        }

        return null;
    }
}
