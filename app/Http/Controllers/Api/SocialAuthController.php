<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Get Google OAuth redirect URL for client-side
     */
    public function getGoogleAuthUrl()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'success' => true,
            'auth_url' => $url
        ]);
    }

    /**
     * Handle Google OAuth callback and return API token
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Get user from Google using the code
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            // Find or create user
            $user = $this->findOrCreateUser($googleUser, 'google');

            // Create Sanctum token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in with Google',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to login with Google. Please try again.',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Handle Telegram authentication from client-side
     */
    public function handleTelegramAuth(Request $request)
    {
        try {
            $authData = $request->all();

            // Verify Telegram authentication
            if (!$this->verifyTelegramAuth($authData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Telegram authentication data'
                ], 401);
            }

            // Create user data from Telegram
            $telegramUser = (object) [
                'id' => $authData['id'],
                'name' => $authData['first_name'] . (isset($authData['last_name']) ? ' ' . $authData['last_name'] : ''),
                'username' => $authData['username'] ?? null,
                'photo_url' => $authData['photo_url'] ?? null,
            ];

            // Find or create user
            $user = $this->findOrCreateUser($telegramUser, 'telegram');

            // Create Sanctum token
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged in with Telegram',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ],
                'token' => $token
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to login with Telegram. Please try again.',
                'error' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Find or create user from social provider
     */
    private function findOrCreateUser($providerUser, $provider)
    {
        // Check if user exists with this provider
        $user = User::where('provider', $provider)
            ->where('provider_id', $providerUser->id)
            ->first();

        if ($user) {
            // Update user info
            $user->update([
                'provider_token' => $providerUser->token ?? null,
                'last_login' => now(),
            ]);
            return $user;
        }

        // Check if user exists with this email
        $email = $provider === 'google' ? $providerUser->email : ($providerUser->id . '@telegram.user');
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // Link social account to existing user
            $existingUser->update([
                'provider' => $provider,
                'provider_id' => $providerUser->id,
                'provider_token' => $providerUser->token ?? null,
                'last_login' => now(),
            ]);
            return $existingUser;
        }

        // Create new user
        $user = User::create([
            'name' => $providerUser->name ?? $providerUser->username ?? 'User',
            'email' => $email,
            'password' => Hash::make(Str::random(32)), // Random password
            'email_verified_at' => now(), // Auto-verify for social logins
            'provider' => $provider,
            'provider_id' => $providerUser->id,
            'provider_token' => $providerUser->token ?? null,
            'avatar' => $providerUser->avatar ?? $providerUser->photo_url ?? null,
            'last_login' => now(),
        ]);

        return $user;
    }

    /**
     * Verify Telegram authentication data
     */
    private function verifyTelegramAuth($authData)
    {
        $checkHash = $authData['hash'] ?? '';
        unset($authData['hash']);

        $dataCheckArr = [];
        foreach ($authData as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);

        $dataCheckString = implode("\n", $dataCheckArr);
        $secretKey = hash('sha256', config('services.telegram.bot_token'), true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);

        // Check if hash matches and auth is not older than 24 hours
        $authTime = $authData['auth_date'] ?? 0;
        $isRecent = (time() - $authTime) < 86400;

        return hash_equals($hash, $checkHash) && $isRecent;
    }
}
