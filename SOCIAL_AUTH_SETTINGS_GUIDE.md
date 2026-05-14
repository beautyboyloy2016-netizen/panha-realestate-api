# Social Authentication Settings Guide

## Overview

The authentication system now supports **dynamic enable/disable** of social login providers (Google and Telegram) through the admin settings panel. Administrators can control which social login options are available to users without modifying code.

## Features

### 1. **Authentication Settings**
Located in the **Admin Settings** panel under the **Authentication** tab:

- **Social Authentication Master Switch** (`auth.social.enabled`)
  - Enable/disable all social authentication methods
  - When disabled, no social login buttons will appear

- **Google Login** (`auth.google.enabled`)
  - Enable/disable Google OAuth login
  - Requires master switch to be enabled

- **Telegram Login** (`auth.telegram.enabled`)
  - Enable/disable Telegram authentication
  - Requires master switch to be enabled

- **User Registration** (`auth.registration.enabled`)
  - Enable/disable new user registrations

- **Email Verification** (`auth.email_verification.required`)
  - Require email verification for new accounts

### 2. **Live Toggle**
Authentication settings feature **auto-save** functionality:
- Toggle switches update immediately
- Visual feedback (yellow → green)
- Toast notifications confirm changes
- No need to click "Save Settings" button

### 3. **Frontend Integration**
Login page automatically adapts based on settings:
- Social login buttons only shown when enabled
- Telegram modal only loads when Telegram is enabled
- Seamless user experience

## Configuration

### Database Settings

Settings are stored in the `settings` table:

```php
'auth.social.enabled' => true,        // Master switch for social auth
'auth.google.enabled' => true,        // Google login toggle
'auth.telegram.enabled' => true,      // Telegram login toggle
'auth.registration.enabled' => true,  // User registration toggle
'auth.email_verification.required' => false // Email verification requirement
```

### Environment Variables

Social login still requires proper configuration in `.env`:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_bot_token
TELEGRAM_BOT_USERNAME=your_bot_username
```

## Usage

### For Administrators

1. **Access Settings Panel**
   - Navigate to `/admin/settings`
   - Click on the **Authentication** tab

2. **Enable/Disable Social Logins**
   - Toggle the switches for desired providers
   - Changes apply immediately
   - Users will see updated login options instantly

3. **Monitor Status**
   - Green badge = Enabled
   - Settings are cached for performance
   - Click "Clear Cache" if needed

### For Developers

#### Check if Social Login is Enabled

```php
use App\Models\Setting;

// Check if any social login is enabled
if (Setting::isSocialAuthEnabled()) {
    // Social auth is available
}

// Check specific providers
if (Setting::isGoogleLoginEnabled()) {
    // Google login is available
}

if (Setting::isTelegramLoginEnabled()) {
    // Telegram login is available
}
```

#### In Blade Templates

```blade
@if(\App\Models\Setting::isGoogleLoginEnabled())
    <a href="{{ route('auth.google') }}" class="btn btn-google">
        Login with Google
    </a>
@endif

@if(\App\Models\Setting::isTelegramLoginEnabled())
    <button id="telegramLogin" class="btn btn-telegram">
        Login with Telegram
    </button>
@endif
```

#### In Controllers

```php
use App\Models\Setting;

public function redirectToGoogle()
{
    // Check if Google login is enabled
    if (!Setting::isGoogleLoginEnabled()) {
        return redirect('/login')
            ->with('error', 'Google login is currently disabled.');
    }

    return Socialite::driver('google')->redirect();
}
```

## API Endpoints

### Toggle Setting (AJAX)
```
POST /admin/settings/toggle
```

**Request:**
```json
{
    "key": "auth.google.enabled",
    "value": true
}
```

**Response:**
```json
{
    "success": true,
    "message": "Setting toggled successfully!",
    "value": true
}
```

### Update Single Setting (AJAX)
```
POST /admin/settings/update-single
```

**Request:**
```json
{
    "key": "auth.google.enabled",
    "value": "1"
}
```

### Clear Settings Cache
```
POST /admin/settings/clear-cache
```

## Security Considerations

### 1. **Critical Settings Protection**
- Authentication settings cannot be deleted
- Only super admins can modify authentication settings
- Changes are logged for audit purposes

### 2. **Fail-Safe Defaults**
If settings are missing from database:
```php
Setting::isSocialAuthEnabled()      // Returns true by default
Setting::isGoogleLoginEnabled()     // Returns true by default
Setting::isTelegramLoginEnabled()   // Returns true by default
```

### 3. **Cache Management**
- Settings cached for 1 hour
- Cache automatically cleared on update
- Manual cache clear available in admin panel

## Troubleshooting

### Social Login Buttons Not Showing

1. **Check Master Switch**
   - Ensure `auth.social.enabled` is ON

2. **Check Provider Settings**
   - Verify `auth.google.enabled` or `auth.telegram.enabled` is ON

3. **Clear Cache**
   - Click "Clear Cache" in settings panel
   - Or run: `php artisan cache:clear`

4. **Check Environment**
   - Verify `.env` has correct credentials
   - Ensure services are properly configured

### "Login Disabled" Error

This occurs when:
- User tries to access social login when disabled
- Settings were changed while user was on login page

**Solution:** User should refresh the page to see updated options

### Settings Not Saving

1. **Check Permissions**
   - User needs `setting_edit` permission
   - Verify role permissions in admin panel

2. **Check Database**
   - Ensure `settings` table exists
   - Run: `php artisan migrate`

3. **Check Logs**
   - Review `storage/logs/laravel.log`
   - Check for validation errors

## Best Practices

### 1. **Enable/Disable Gradually**
- Test with small user group first
- Monitor error logs after changes
- Communicate changes to users

### 2. **Keep Environment Configured**
- Maintain `.env` credentials even when disabled
- Allows quick re-enabling without reconfiguration

### 3. **Regular Cache Clearing**
- Clear cache after multiple setting changes
- Especially after authentication modifications

### 4. **Backup Before Changes**
- Export settings before major changes
- Keep record of previous configurations

## Technical Implementation

### Model Methods

**Setting Model** (`app/Models/Setting.php`):
```php
// Get setting value
Setting::get('auth.google.enabled', true);

// Set setting value
Setting::set('auth.google.enabled', true);

// Helper methods
Setting::isSocialAuthEnabled();
Setting::isGoogleLoginEnabled();
Setting::isTelegramLoginEnabled();
```

### Controller Methods

**SocialAuthController** checks settings before processing:
```php
public function redirectToGoogle()
{
    if (!Setting::isGoogleLoginEnabled()) {
        return redirect('/login')
            ->with('error', 'Google login is currently disabled.');
    }
    
    return Socialite::driver('google')->redirect();
}
```

### View Integration

**Login View** (`resources/views/auth/login.blade.php`):
```blade
@php
    $googleEnabled = \App\Models\Setting::isGoogleLoginEnabled();
    $telegramEnabled = \App\Models\Setting::isTelegramLoginEnabled();
@endphp

@if($googleEnabled || $telegramEnabled)
    <!-- Show social login section -->
@endif
```

## Migration & Seeding

### Settings Migration
Already exists: `2025_11_27_050000_create_settings_table.php`

### Seeding Authentication Settings
```bash
php artisan db:seed --class=SettingsSeeder
```

This creates:
- `auth.social.enabled` (boolean)
- `auth.google.enabled` (boolean)
- `auth.telegram.enabled` (boolean)
- `auth.registration.enabled` (boolean)
- `auth.email_verification.required` (boolean)

## Future Enhancements

Potential improvements:
- Add more social providers (Facebook, Twitter, LinkedIn)
- Role-based social login restrictions
- Provider-specific settings (auto-registration, email domain restrictions)
- Social login analytics and usage tracking
- Scheduled enable/disable (maintenance mode)
- A/B testing for social providers

## Support

For issues or questions:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify database settings: `SELECT * FROM settings WHERE group = 'authentication'`
3. Test in incognito mode to rule out cache issues
4. Review this documentation for common solutions
