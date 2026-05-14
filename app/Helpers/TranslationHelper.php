<?php

namespace App\Helpers;

use Illuminate\Support\Facades\App;

class TranslationHelper
{
    /**
     * Get available locales
     */
    public static function getAvailableLocales()
    {
        return config('app.available_locales', ['en', 'kh']);
    }

    /**
     * Get locale name
     */
    public static function getLocaleName($locale)
    {
        $names = [
            'en' => 'English',
            'kh' => 'ខ្មែរ',
        ];

        return $names[$locale] ?? $locale;
    }

    /**
     * Get flag for locale
     */
    public static function getLocaleFlag($locale)
    {
        $flags = [
            'en' => '🇺🇸',
            'kh' => '🇰🇭',
        ];

        return $flags[$locale] ?? '🏳️';
    }

    /**
     * Check if locale is RTL
     */
    public static function isRtl($locale = null)
    {
        $locale = $locale ?: App::getLocale();
        $rtlLocales = ['ar', 'he', 'fa', 'ur'];

        return in_array($locale, $rtlLocales);
    }
}
