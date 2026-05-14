# Language Switcher Implementation Guide

## Overview

A complete multi-language system for the Panha Real Estate API, supporting English (EN) and Khmer (KM) with seamless switching and user preference persistence.

## Architecture Components

### 1. Middleware: `SetLocale.php`
**Location:** `app/Http/Middleware/SetLocale.php`

**Purpose:** Automatically detect and set the application locale for each request.

**Priority Chain:**
1. URL parameter (`?locale=km`)
2. Session storage
3. Authenticated user preference
4. Browser `Accept-Language` header
5. Default locale from config

**Key Methods:**
- `handle($request, $next)` - Main middleware logic
- `getBrowserLocale($request)` - Parses HTTP_ACCEPT_LANGUAGE header

**Registration:** 
```php
// bootstrap/app.php
$middleware->web(append: [
    \App\Http\Middleware\SetLocale::class,
]);
```

### 2. Controller: `LanguageController.php`
**Location:** `app/Http/Controllers/LanguageController.php`

**Methods:**

#### `switch($locale)`
- **Route:** `GET /language/{locale}`
- **Name:** `language.switch`
- **Purpose:** Changes application language
- **Actions:**
  - Validates locale against `config('app.available_locales')`
  - Updates session: `session(['locale' => $locale])`
  - Updates authenticated user: `auth()->user()->update(['locale' => $locale])`
  - Redirects back with success message

#### `current()`
- **Purpose:** API endpoint for current locale info
- **Returns:** JSON with `current_locale` and `available_locales`

**Usage Example:**
```blade
<a href="{{ route('language.switch', 'km') }}">Switch to Khmer</a>
```

### 3. Blade Component: `language-switcher.blade.php`
**Location:** `resources/views/components/language-switcher.blade.php`

**Available Themes:**

#### Admin Theme (Compact Dropdown)
```blade
<x-language-switcher theme="admin" />
```
- Compact button with flag emoji and short code (🇺🇸 EN)
- Dropdown with checkmark for current selection
- Best for: Admin headers, tight spaces

#### Compact Theme (Icon Buttons)
```blade
<x-language-switcher theme="compact" />
```
- Button group with flag emojis only
- Active state with primary background
- Best for: Mobile views, icon-only interfaces

#### Tabs Theme (Nav Pills)
```blade
<x-language-switcher theme="tabs" />
```
- Navigation pills style
- Full language names with flags
- Best for: Settings pages, language preferences

#### Default Theme (Full Dropdown)
```blade
<x-language-switcher theme="default" />
```
- Globe icon with full dropdown
- Shows full language names
- Best for: Public-facing pages

**Supported Locales:**
- `en` - English 🇺🇸
- `km` - ខ្មែរ (Khmer) 🇰🇭

## Configuration

### 1. Config File: `config/app.php`
```php
'locale' => env('APP_LOCALE', 'en'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
'available_locales' => ['en', 'km'],
```

### 2. Routes: `routes/web.php`
```php
use App\Http\Controllers\LanguageController;

Route::get('/language/{locale}', [LanguageController::class, 'switch'])
    ->name('language.switch');
```

### 3. Database: `users` Table
```php
// Migration: 2025_11_29_032333_add_locale_to_users_table.php
Schema::table('users', function (Blueprint $table) {
    $table->string('locale', 2)->default('en')->after('avatar');
});

// Model: app/Models/User.php
protected $fillable = [
    // ... other fields
    'locale',
];
```

## Translation Files

### Structure
```
resources/lang/
├── en/
│   ├── common.php       # Common UI strings
│   ├── admin.php        # Admin-specific translations
│   └── langauages.php   # Existing file
└── km/
    ├── common.php       # Khmer common strings
    ├── admin.php        # Khmer admin translations
    └── langauages.php   # Existing file
```

### Common Translations (`common.php`)
**Available Keys:**
- `dashboard`, `welcome`, `logout`, `profile`, `settings`
- `search`, `actions`, `edit`, `delete`, `create`
- `save`, `cancel`, `back`, `submit`, `confirm`
- `yes`, `no`, `status`, `active`, `inactive`
- `success`, `error`, `warning`, `info`

**Usage in Blade:**
```blade
{{ __('common.edit') }}        <!-- Output: Edit / កែសម្រួល -->
{{ __('common.save') }}        <!-- Output: Save / រក្សាទុក -->
{{ __('common.active') }}      <!-- Output: Active / សកម្ម -->
```

### Admin Translations (`admin.php`)
**Available Keys:**

**Menu:**
- `admin.menu.dashboard`, `admin.menu.users`, `admin.menu.roles`
- `admin.menu.properties`, `admin.menu.projects`, `admin.menu.news_articles`

**Properties:**
- `admin.properties.listing_type`, `admin.properties.price`
- `admin.properties.bedrooms`, `admin.properties.bathrooms`

**Users:**
- `admin.users.first_name`, `admin.users.last_name`
- `admin.users.email`, `admin.users.role`

**Usage in Blade:**
```blade
{{ __('admin.menu.properties') }}           <!-- Output: Properties / អចលនទ្រព្យ -->
{{ __('admin.properties.bedrooms') }}       <!-- Output: Bedrooms / បន្ទប់គេង -->
```

## Integration Examples

### 1. Admin Header Integration
**File:** `resources/views/admin/layouts/partials/top_header.blade.php`

```blade
<!-- Fullscreen Toggle -->
<button class="btn btn-link text-secondary p-2 d-none d-sm-inline-block" id="fullscreen-btn">
    <i class="fa-solid fa-expand"></i>
</button>

<!-- Language Switcher -->
<x-language-switcher theme="admin" />

<!-- Profile Dropdown -->
<div class="position-relative ms-3" id="profile-dropdown-container">
    ...
</div>
```

### 2. Dashboard Demo
**File:** `resources/views/admin/dashboard.blade.php`

```blade
@extends('admin.layouts.master_layout')

@section('content')
<div class="card">
    <div class="card-body">
        <h3>{{ __('common.welcome') }}</h3>
        <p>{{ __('common.dashboard') }} - Current: {{ app()->getLocale() }}</p>
        
        <!-- Show all themes -->
        <x-language-switcher theme="admin" />
        <x-language-switcher theme="compact" />
        <x-language-switcher theme="tabs" />
        <x-language-switcher theme="default" />
    </div>
</div>
@endsection
```

### 3. Using Translations in Controllers
```php
// Success message
return redirect()->back()->with('success', __('common.success'));

// Error message
return response()->json(['error' => __('common.error')], 400);

// Validation
$request->validate([
    'title' => 'required|max:255',
], [
    'title.required' => __('validation.required', ['attribute' => __('admin.properties.title')]),
]);
```

### 4. Using Translations in JavaScript
```javascript
// Get current locale
const currentLocale = document.documentElement.lang || 'en';

// Fetch translations via API
fetch('/api/language/current')
    .then(response => response.json())
    .then(data => {
        console.log('Current:', data.current_locale);
        console.log('Available:', data.available_locales);
    });
```

## Testing the Implementation

### 1. Test Switching via URL
```
http://localhost/admin/dashboard
http://localhost/language/km (switches to Khmer)
http://localhost/language/en (switches to English)
```

### 2. Test Session Persistence
1. Switch language via dropdown
2. Navigate to another page
3. Verify language persists

### 3. Test User Preference
1. Login as user
2. Switch language
3. Check `users.locale` column in database
4. Logout and login again - preference should persist

### 4. Test Browser Locale
1. Clear session: `php artisan cache:clear`
2. Set browser language to Khmer
3. Visit site - should auto-detect Khmer

### 5. Test All Themes
Visit `/admin/dashboard` to see all 4 themes:
- Admin theme in header
- All 4 themes displayed in dashboard card

## Extending the System

### Adding New Locales

**Step 1:** Add to config
```php
// config/app.php
'available_locales' => ['en', 'km', 'th'], // Added Thai
```

**Step 2:** Create translation directory
```bash
mkdir resources/lang/th
```

**Step 3:** Copy translation files
```bash
cp resources/lang/en/common.php resources/lang/th/common.php
cp resources/lang/en/admin.php resources/lang/th/admin.php
```

**Step 4:** Update language-switcher component
```blade
@php
$locales = [
    'en' => ['name' => 'English', 'flag' => '🇺🇸', 'short' => 'EN'],
    'km' => ['name' => 'ខ្មែរ', 'flag' => '🇰🇭', 'short' => 'KM'],
    'th' => ['name' => 'ไทย', 'flag' => '🇹🇭', 'short' => 'TH'], // New
];
@endphp
```

### Adding New Translation Keys

**Step 1:** Add to English file
```php
// resources/lang/en/common.php
return [
    // ... existing keys
    'my_new_key' => 'My New Translation',
];
```

**Step 2:** Add Khmer translation
```php
// resources/lang/km/common.php
return [
    // ... existing keys
    'my_new_key' => 'ការបកប្រែថ្មីរបស់ខ្ញុំ',
];
```

**Step 3:** Use in Blade
```blade
{{ __('common.my_new_key') }}
```

### Creating New Translation Files

**Example: Property-specific translations**

```php
// resources/lang/en/properties.php
return [
    'for_sale' => 'For Sale',
    'for_rent' => 'For Rent',
    'apartment' => 'Apartment',
    'house' => 'House',
    'condo' => 'Condominium',
    'land' => 'Land',
];

// resources/lang/km/properties.php
return [
    'for_sale' => 'សម្រាប់លក់',
    'for_rent' => 'សម្រាប់ជួល',
    'apartment' => 'អាផាតមិន',
    'house' => 'ផ្ទះ',
    'condo' => 'កុងដូ',
    'land' => 'ដី',
];

// Usage
{{ __('properties.for_sale') }}
```

## Troubleshooting

### Issue: Translations not working
**Solution:**
```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
```

### Issue: Component not found
**Solution:**
```bash
# Check file exists
ls resources/views/components/language-switcher.blade.php

# Clear compiled views
php artisan view:clear
```

### Issue: Locale not persisting
**Solution:**
1. Check session configuration in `config/session.php`
2. Verify middleware is registered in `bootstrap/app.php`
3. Check `users.locale` column exists: `php artisan migrate`

### Issue: Wrong locale displayed
**Solution:**
1. Check priority chain in SetLocale middleware
2. Verify `config/app.php` has `available_locales` array
3. Clear session: `php artisan cache:clear`

## Best Practices

### 1. Always Use Translation Keys
**Bad:**
```blade
<button>Delete</button>
```

**Good:**
```blade
<button>{{ __('common.delete') }}</button>
```

### 2. Use Nested Keys for Organization
```php
// Good structure
'admin.properties.create'
'admin.users.edit'
'frontend.home.welcome'

// Poor structure
'create_property'
'edit_user'
'welcome_message'
```

### 3. Provide Fallback Text
```blade
{{ __('admin.properties.title', [], 'Property Title') }}
```

### 4. Use Parameters in Translations
```php
// Translation file
'welcome_user' => 'Welcome, :name!',

// Usage
{{ __('common.welcome_user', ['name' => $user->first_name]) }}
```

### 5. Cache Translations in Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Summary

✅ **Implemented:**
- SetLocale middleware with 5-tier priority detection
- LanguageController with switch() and current() methods
- Blade component with 4 different themes
- Translation files for en/km (common.php, admin.php)
- Users table locale column
- Full integration in admin header
- Demo dashboard with all themes

✅ **Features:**
- Session-based persistence
- User preference storage in database
- Browser locale detection
- URL parameter override
- Multiple UI themes (admin, compact, tabs, default)
- Bilingual support (EN/KM)
- Extensible architecture

✅ **Ready for:**
- Adding more languages (Thai, Vietnamese, etc.)
- Creating domain-specific translation files
- API-based language switching
- Frontend integration
