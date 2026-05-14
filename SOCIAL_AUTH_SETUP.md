# Social Authentication Setup (Google & Telegram)

## ✅ What Has Been Implemented

### 1. Database Changes
- ✅ Added migration with social auth fields: `provider`, `provider_id`, `provider_token`, `provider_refresh_token`
- ✅ Migration run successfully

### 2. Configuration
- ✅ Updated `config/services.php` with Google and Telegram config
- ✅ Updated User model with new fillable fields

### 3. Controller & Routes
- ✅ Created `SocialAuthController` with:
  - Google OAuth redirect and callback
  - Telegram authentication handler
  - Auto-create/link user accounts
- ✅ Added routes in `routes/auth.php`:
  - `GET /auth/google` - Redirect to Google
  - `GET /auth/google/callback` - Google callback
  - `POST /auth/telegram` - Telegram auth handler

## 📦 Installation Steps

### Step 1: Install Laravel Socialite

```bash
composer require laravel/socialite
```

### Step 2: Configure Environment Variables

Add these to your `.env` file:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=${APP_URL}/auth/google/callback

# Telegram Bot
TELEGRAM_BOT_TOKEN=your_telegram_bot_token_here
TELEGRAM_BOT_USERNAME=your_bot_username
```

### Step 3: Setup Google OAuth

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable Google+ API
4. Go to "Credentials" → "Create Credentials" → "OAuth 2.0 Client ID"
5. Set authorized redirect URI: `http://localhost:8000/auth/google/callback`
6. Copy Client ID and Client Secret to `.env`

### Step 4: Setup Telegram Bot

1. Open Telegram and search for [@BotFather](https://t.me/BotFather)
2. Send `/newbot` command
3. Follow instructions to create bot
4. Copy the bot token to `.env`
5. Set bot username in `.env`

### Step 5: Add Login Buttons to Your Login View

Add these buttons to your login form (e.g., `resources/views/auth/login.blade.php`):

```blade
<!-- Social Login Section -->
<div class="mt-4">
    <div class="text-center mb-3">
        <span class="text-muted">Or continue with</span>
    </div>
    
    <div class="d-grid gap-2">
        <!-- Google Login Button -->
        <a href="{{ route('auth.google') }}" class="btn btn-outline-danger">
            <i class="fab fa-google me-2"></i> Continue with Google
        </a>
        
        <!-- Telegram Login Button -->
        <button type="button" class="btn btn-outline-info" id="telegramLoginBtn">
            <i class="fab fa-telegram me-2"></i> Continue with Telegram
        </button>
    </div>
</div>

<!-- Telegram Login Widget (Hidden) -->
<div id="telegram-login-container" style="display: none;">
    <script async src="https://telegram.org/js/telegram-widget.js?22" 
        data-telegram-login="{{ config('services.telegram.bot_username') }}" 
        data-size="large" 
        data-auth-url="{{ route('auth.telegram') }}" 
        data-request-access="write">
    </script>
</div>

@push('scripts')
<script>
    // Telegram Login Handler
    document.getElementById('telegramLoginBtn')?.addEventListener('click', function() {
        // Open Telegram widget
        const container = document.getElementById('telegram-login-container');
        if (container) {
            container.style.display = 'block';
            // Auto-click the Telegram button
            setTimeout(() => {
                const telegramBtn = container.querySelector('iframe');
                if (telegramBtn) {
                    telegramBtn.click();
                }
            }, 100);
        }
    });
    
    // Handle Telegram callback
    window.onTelegramAuth = function(user) {
        fetch('{{ route('auth.telegram') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify(user)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect;
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Login failed. Please try again.');
        });
    };
</script>
@endpush
```

## 🎨 Features

### Auto-Account Linking
- If user logs in with Google/Telegram and email exists → links social account
- If new user → creates account automatically
- Sets random password for social-only users
- Auto-verifies email for social users

### User Data Stored
- `provider`: 'google' or 'telegram'
- `provider_id`: Unique ID from provider
- `provider_token`: OAuth access token (for API calls)
- `provider_refresh_token`: Refresh token (if available)

### Security
- Telegram auth data verification using HMAC-SHA256
- Auth data expiration check (24 hours)
- CSRF protection on all endpoints

## 🧪 Testing

### Test Google Login:
1. Click "Continue with Google" button
2. Authorize the app
3. Should redirect to dashboard
4. Check `users` table for new user with `provider='google'`

### Test Telegram Login:
1. Click "Continue with Telegram" button
2. Authorize through Telegram
3. Should redirect to dashboard
4. Check `users` table for new user with `provider='telegram'`

## 📝 Notes

- **Password**: Social users get random 24-character password
- **Email**: Telegram users get synthetic email: `{telegram_id}@telegram.user`
- **Username**: Generated as `{provider}_{provider_id}`
- **Avatar**: Automatically imported from social provider
- **Email Verification**: Auto-verified for social logins

## 🔧 Troubleshooting

**Issue**: Google OAuth error
- ✅ Check redirect URI matches exactly
- ✅ Ensure Google+ API is enabled
- ✅ Check credentials in `.env`

**Issue**: Telegram widget not showing
- ✅ Verify bot token in `.env`
- ✅ Check bot username is correct
- ✅ Ensure bot is active

**Issue**: "Invalid Telegram authentication"
- ✅ Check bot token is correct
- ✅ Verify auth data is recent (< 24 hours)

## 🚀 Next Steps

1. Run `composer require laravel/socialite`
2. Add credentials to `.env`
3. Add login buttons to your login view
4. Test both Google and Telegram login
5. Customize success redirect in controller if needed

## 📚 API Routes

```
GET  /auth/google              - Redirect to Google OAuth
GET  /auth/google/callback     - Handle Google callback
POST /auth/telegram            - Handle Telegram auth data
```

## 🎯 Success Messages

All social logins show success toast notifications:
- Google: "🎉 Successfully logged in with Google!"
- Telegram: "🎉 Successfully logged in with Telegram!"
