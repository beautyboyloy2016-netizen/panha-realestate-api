<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Switch application language
     */
    public function switch(Request $request, $locale)
    {
        // Get available locales
        $availableLocales = config('app.available_locales', ['en', 'km']);

        // Validate locale
        if (!in_array($locale, $availableLocales)) {
            return back()->with('error', 'Invalid language selected');
        }

        // Set locale in session
        Session::put('locale', $locale);
        App::setLocale($locale);

        // Update user preference if authenticated
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        // Redirect back with success message
        return back()->with('success', 'Language changed successfully');
    }

    /**
     * Get current locale (for API)
     */
    public function current()
    {
        return response()->json([
            'current_locale' => App::getLocale(),
            'available_locales' => config('app.available_locales', ['en', 'km'])
        ]);
    }
}
