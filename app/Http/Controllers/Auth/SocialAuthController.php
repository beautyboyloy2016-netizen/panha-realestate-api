<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        // Check if Google login is enabled
        if (!Setting::isGoogleLoginEnabled()) {
            return redirect('/login')
                ->with('error', 'Google login is currently disabled.');
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        // Check if Google login is enabled
        if (!Setting::isGoogleLoginEnabled()) {
            return redirect('/login')
                ->with('error', 'Google login is currently disabled.');
        }

        try {
            $googleUser = Socialite::driver('google')->user();

            // Find or create user
            $user = $this->findOrCreateUser($googleUser, 'google');

            // Log the user in
            Auth::login($user, true);

            // Update last login
            $user->update(['last_login' => now()]);

            return redirect()->intended('/dashboard')
                ->with('success', '🎉 Successfully logged in with Google!');

        } catch (\Exception $e) {
            return redirect('/login')
                ->with('error', 'Unable to login with Google. Please try again.');
        }
    }

    /**
     * Handle Telegram authentication
     */
    public function handleTelegramAuth(Request $request)
    {
        // Check if Telegram login is enabled
        if (!Setting::isTelegramLoginEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Telegram login is currently disabled.',
            ], 403);
        }

        $request->validate([
            'id' => 'required',
            'first_name' => 'required',
            'auth_date' => 'required',
            'hash' => 'required',
        ]);

        // Verify Telegram data
        if (!$this->verifyTelegramAuth($request->all())) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Telegram authentication data',
            ], 401);
        }

        try {
            // Create user data from Telegram
            $telegramData = [
                'id' => $request->id,
                'email' => $request->id . '@telegram.user', // Telegram doesn't provide email
                'name' => $request->first_name . ' ' . ($request->last_name ?? ''),
                'avatar' => $request->photo_url ?? null,
                'token' => null,
            ];

            // Convert to object for consistency
            $telegramUser = (object) $telegramData;

            // Find or create user
            $user = $this->findOrCreateUser($telegramUser, 'telegram');

            // Log the user in
            Auth::login($user, true);

            // Update last login
            $user->update(['last_login' => now()]);

            return response()->json([
                'success' => true,
                'message' => '🎉 Successfully logged in with Telegram!',
                'redirect' => '/dashboard',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to login with Telegram. Please try again.',
            ], 500);
        }
    }

    /**
     * Find or create user from social provider
     */
    private function findOrCreateUser($providerUser, $provider)
    {
        // Try to find user by provider ID
        $user = User::where('provider', $provider)
            ->where('provider_id', $providerUser->id)
            ->first();

        if ($user) {
            // Update provider token
            $user->update([
                'provider_token' => $providerUser->token ?? null,
                'provider_refresh_token' => $providerUser->refreshToken ?? null,
            ]);

            return $user;
        }

        // Check if user exists with this email
        $email = $provider === 'telegram' ? $providerUser->id . '@telegram.user' : $providerUser->email;
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // Link social account to existing user
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $providerUser->id,
                'provider_token' => $providerUser->token ?? null,
                'provider_refresh_token' => $providerUser->refreshToken ?? null,
            ]);

            return $existingUser;
        }

        // Create new user
        $name = $provider === 'google' ? explode(' ', $providerUser->name) : explode(' ', $providerUser->name);

        return User::create([
            'first_name' => $name[0] ?? 'User',
            'last_name' => $name[1] ?? '',
            'username' => $provider . '_' . $providerUser->id,
            'email' => $email,
            'password' => Hash::make(Str::random(24)), // Random password for social users
            'avatar' => $providerUser->avatar ?? null,
            'provider' => $provider,
            'provider_id' => $providerUser->id,
            'provider_token' => $providerUser->token ?? null,
            'provider_refresh_token' => $providerUser->refreshToken ?? null,
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);
    }

    /**
     * Verify Telegram authentication data
     */
    private function verifyTelegramAuth($authData)
    {
        $checkHash = $authData['hash'];
        unset($authData['hash']);

        $dataCheckArr = [];
        foreach ($authData as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);

        $dataCheckString = implode("\n", $dataCheckArr);
        $secretKey = hash('sha256', config('services.telegram.bot_token'), true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // Check if auth data is recent (within 1 day)
        if ((time() - $authData['auth_date']) > 86400) {
            return false;
        }

        return strcmp($hash, $checkHash) === 0;
    }
}
