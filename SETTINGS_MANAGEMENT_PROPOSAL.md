# Settings Management System - Panha Real Estate API

## Overview
A comprehensive settings management system for configuring the real estate platform's behavior, appearance, and business rules.

---

## 📋 Database Schema

### `settings` Table
```php
Schema::create('settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique(); // e.g., 'site.name', 'currency.default'
    $table->text('value')->nullable();
    $table->string('type')->default('string'); // string, boolean, integer, json, file
    $table->string('group')->default('general'); // general, site, mail, payment, etc.
    $table->text('description')->nullable();
    $table->boolean('is_public')->default(false); // Can be accessed via public API
    $table->timestamps();
});
```

---

## 🎯 Settings Categories

### 1. **General Settings** (`group: 'general'`)
```php
'site.name' => 'Panha Real Estate'
'site.tagline' => 'Find Your Dream Property in Cambodia'
'site.logo' => '/storage/logo.png'
'site.favicon' => '/storage/favicon.ico'
'site.description' => 'Cambodia\'s leading real estate platform'
'site.keywords' => 'real estate, Cambodia, property, buy, rent, sell'
'contact.email' => 'info@panhaestate.com'
'contact.phone' => '+855 12 345 678'
'contact.address' => 'Phnom Penh, Cambodia'
'social.facebook' => 'https://facebook.com/panhaestate'
'social.instagram' => 'https://instagram.com/panhaestate'
'social.telegram' => 'https://t.me/panhaestate'
'social.youtube' => 'https://youtube.com/@panhaestate'
```

### 2. **Currency & Localization** (`group: 'localization'`)
```php
'currency.default' => 'USD'
'currency.secondary' => 'KHR'
'currency.exchange_rate' => '4100' // KHR per 1 USD
'currency.symbol' => '$'
'currency.position' => 'before' // before or after amount
'language.default' => 'en'
'language.available' => ['en', 'km']
'language.fallback' => 'en'
'timezone.default' => 'Asia/Phnom_Penh'
'date.format' => 'd/m/Y'
'time.format' => 'H:i'
```

### 3. **Property Settings** (`group: 'property'`)
```php
'property.listing_duration' => 90 // days
'property.max_images' => 20
'property.max_videos' => 5
'property.featured_price' => 50 // USD for featured listing
'property.premium_price' => 100 // USD for premium listing
'property.auto_renew' => true
'property.moderation_enabled' => true
'property.require_verification' => false
'property.min_price' => 1000
'property.max_price' => 10000000
'property.default_listing_type' => 'sale'
'property.allowed_types' => ['apartment', 'house', 'condo', 'villa', 'land', 'commercial']
```

### 4. **Payment Settings** (`group: 'payment'`)
```php
'payment.enabled' => true
'payment.gateway' => 'stripe' // stripe, paypal, aba
'payment.test_mode' => true
'payment.commission_rate' => 5 // percentage
'payment.min_amount' => 10
'payment.max_amount' => 100000
'payment.aba.merchant_id' => ''
'payment.stripe.public_key' => ''
'payment.stripe.secret_key' => ''
```

### 5. **Email Settings** (`group: 'mail'`)
```php
'mail.enabled' => true
'mail.from_name' => 'Panha Real Estate'
'mail.from_address' => 'noreply@panhaestate.com'
'mail.driver' => 'smtp'
'mail.notifications.new_listing' => true
'mail.notifications.inquiry' => true
'mail.notifications.favorites' => false
'mail.templates.welcome' => 'emails.welcome'
'mail.templates.inquiry' => 'emails.inquiry'
```

### 6. **SEO Settings** (`group: 'seo'`)
```php
'seo.meta_title' => 'Panha Real Estate - Properties in Cambodia'
'seo.meta_description' => 'Find and list properties for sale and rent in Cambodia'
'seo.meta_keywords' => 'real estate, property, Cambodia, Phnom Penh'
'seo.og_image' => '/storage/og-image.jpg'
'seo.google_analytics_id' => 'G-XXXXXXXXXX'
'seo.facebook_pixel_id' => ''
'seo.robots.txt' => 'User-agent: *\nAllow: /'
'seo.sitemap_enabled' => true
'seo.canonical_url' => 'https://panhaestate.com'
```

### 7. **Media Settings** (`group: 'media'`)
```php
'media.max_upload_size' => 10240 // KB (10MB)
'media.allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp']
'media.image_quality' => 85 // 1-100
'media.thumbnail_width' => 300
'media.thumbnail_height' => 300
'media.watermark_enabled' => false
'media.watermark_image' => '/storage/watermark.png'
'media.watermark_position' => 'bottom-right'
'media.storage_driver' => 'local' // local, s3, cloudinary
```

### 8. **Security Settings** (`group: 'security'`)
```php
'security.recaptcha_enabled' => true
'security.recaptcha_site_key' => ''
'security.recaptcha_secret_key' => ''
'security.rate_limit.api' => 60 // requests per minute
'security.rate_limit.login' => 5
'security.password_min_length' => 8
'security.password_require_uppercase' => true
'security.password_require_numbers' => true
'security.password_require_special' => false
'security.session_lifetime' => 120 // minutes
'security.two_factor_enabled' => false
```

### 9. **Map Settings** (`group: 'map'`)
```php
'map.provider' => 'google' // google, mapbox, leaflet
'map.google_api_key' => ''
'map.mapbox_access_token' => ''
'map.default_lat' => 11.5564 // Phnom Penh
'map.default_lng' => 104.9282
'map.default_zoom' => 12
'map.clustering_enabled' => true
'map.marker_icon' => '/storage/map-marker.png'
```

### 10. **Featured & Ads** (`group: 'advertising'`)
```php
'ads.banner_home_top' => ''
'ads.banner_home_bottom' => ''
'ads.banner_listing_sidebar' => ''
'ads.google_adsense_id' => ''
'ads.enabled' => true
'featured.max_properties' => 10
'featured.rotation_enabled' => true
'featured.rotation_interval' => 7 // days
```

---

## 🛠️ Implementation

### Model: `app/Models/Setting.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description', 'is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    // Get setting value with type casting
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'float' => (float) $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    // Set setting value with type handling
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    // Cache settings
    protected static function boot()
    {
        parent::boot();
        
        static::saved(function () {
            Cache::forget('app_settings');
        });
        
        static::deleted(function () {
            Cache::forget('app_settings');
        });
    }
}
```

### Helper: `app/Helpers/SettingsHelper.php`
```php
<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('settings_group')) {
    function settings_group($group)
    {
        return Setting::where('group', $group)->pluck('value', 'key')->toArray();
    }
}

if (!function_exists('update_setting')) {
    function update_setting($key, $value)
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('app_settings');
    }
}
```

### Controller: `app/Http/Controllers/Backend/SettingController.php`
```php
<?php

namespace App\Http\Controllers\Backend;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends BaseController
{
    protected string $resource = 'setting';

    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }
}
```

### API Controller: `app/Http/Controllers/Api/SettingController.php`
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingController extends Controller
{
    // Get public settings for frontend
    public function index()
    {
        $settings = Setting::where('is_public', true)
            ->get()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->pluck('value', 'key');
            });

        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    // Get settings by group
    public function group($group)
    {
        $settings = Setting::where('group', $group)
            ->where('is_public', true)
            ->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }
}
```

---

## 📡 API Routes

### Public Settings API
```php
// routes/api.php
Route::get('/settings', [SettingController::class, 'index']);
Route::get('/settings/{group}', [SettingController::class, 'group']);
```

### Admin Settings Routes
```php
// routes/web.php
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'permission:setting_access']], function () {
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});
```

---

## 🎨 Admin View: `resources/views/admin/settings/index.blade.php`
```blade
<x-app-layout>
    <div class="container-fluid">
        <h1>System Settings</h1>
        
        <form method="POST" action="{{ route('admin.settings.update') }}">
            @csrf
            
            <ul class="nav nav-tabs" role="tablist">
                @foreach($settings as $group => $items)
                    <li class="nav-item">
                        <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                           data-bs-toggle="tab" href="#{{ $group }}">
                            {{ ucfirst($group) }}
                        </a>
                    </li>
                @endforeach
            </ul>
            
            <div class="tab-content">
                @foreach($settings as $group => $items)
                    <div id="{{ $group }}" class="tab-pane {{ $loop->first ? 'active' : '' }}">
                        <div class="card">
                            <div class="card-body">
                                @foreach($items as $setting)
                                    <div class="mb-3">
                                        <label class="form-label">{{ ucfirst(str_replace('_', ' ', str_replace($group.'.', '', $setting->key))) }}</label>
                                        
                                        @if($setting->type === 'boolean')
                                            <div class="form-check form-switch">
                                                <input type="checkbox" class="form-check-input" 
                                                       name="settings[{{ $setting->key }}]" 
                                                       value="1" {{ $setting->value ? 'checked' : '' }}>
                                            </div>
                                        @elseif($setting->type === 'json')
                                            <textarea name="settings[{{ $setting->key }}]" 
                                                      class="form-control" rows="3">{{ json_encode($setting->value) }}</textarea>
                                        @else
                                            <input type="text" class="form-control" 
                                                   name="settings[{{ $setting->key }}]" 
                                                   value="{{ $setting->value }}">
                                        @endif
                                        
                                        @if($setting->description)
                                            <small class="text-muted">{{ $setting->description }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
        </form>
    </div>
</x-app-layout>
```

---

## 🗄️ Migration: `database/migrations/xxxx_create_settings_table.php`
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string');
            $table->string('group')->default('general');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
            
            $table->index(['group', 'key']);
            $table->index('is_public');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
```

---

## 🌱 Seeder: `database/seeders/SettingsSeeder.php`
```php
<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General
            ['key' => 'site.name', 'value' => 'Panha Real Estate', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site.tagline', 'value' => 'Find Your Dream Property in Cambodia', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'site.logo', 'value' => '/storage/logo.png', 'type' => 'file', 'group' => 'general', 'is_public' => true],
            ['key' => 'contact.email', 'value' => 'info@panhaestate.com', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            ['key' => 'contact.phone', 'value' => '+855 12 345 678', 'type' => 'string', 'group' => 'general', 'is_public' => true],
            
            // Currency
            ['key' => 'currency.default', 'value' => 'USD', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
            ['key' => 'currency.symbol', 'value' => '$', 'type' => 'string', 'group' => 'localization', 'is_public' => true],
            ['key' => 'currency.exchange_rate', 'value' => '4100', 'type' => 'integer', 'group' => 'localization', 'is_public' => true],
            
            // Property
            ['key' => 'property.listing_duration', 'value' => '90', 'type' => 'integer', 'group' => 'property', 'is_public' => false],
            ['key' => 'property.max_images', 'value' => '20', 'type' => 'integer', 'group' => 'property', 'is_public' => true],
            ['key' => 'property.featured_price', 'value' => '50', 'type' => 'integer', 'group' => 'property', 'is_public' => true],
            
            // SEO
            ['key' => 'seo.meta_title', 'value' => 'Panha Real Estate - Properties in Cambodia', 'type' => 'string', 'group' => 'seo', 'is_public' => true],
            ['key' => 'seo.google_analytics_id', 'value' => '', 'type' => 'string', 'group' => 'seo', 'is_public' => true],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
```

---

## ✅ Usage Examples

### In Blade Templates
```blade
<title>{{ setting('site.name') }} - {{ setting('site.tagline') }}</title>
<img src="{{ setting('site.logo') }}" alt="Logo">
<p>Contact: {{ setting('contact.email') }}</p>
```

### In Controllers
```php
$currencySymbol = setting('currency.symbol', '$');
$maxImages = setting('property.max_images', 10);
$featuredPrice = setting('property.featured_price', 50);
```

### Via API (Frontend)
```javascript
// Get all public settings
fetch('/api/settings')
  .then(r => r.json())
  .then(data => {
    console.log(data.settings.general['site.name']);
    console.log(data.settings.localization['currency.symbol']);
  });

// Get specific group
fetch('/api/settings/localization')
  .then(r => r.json())
  .then(data => {
    console.log(data.settings['currency.symbol']); // $
  });
```

---

## 🔒 Permissions Required
```php
'setting_access' => 'Access Settings'
'setting_edit' => 'Edit Settings'
'settings.view' => 'View Settings'
'settings.system' => 'System Settings'
```

---

## 🚀 Benefits

1. **Centralized Configuration** - All settings in one place
2. **Type Safety** - Automatic type casting (boolean, integer, json)
3. **Caching** - High performance with cache layer
4. **Public API** - Frontend can access allowed settings
5. **Grouping** - Organized by categories
6. **Extensible** - Easy to add new settings
7. **User-Friendly** - Admin panel with tabs
8. **Version Control** - Track changes in database
9. **Multi-Language** - Can store translations
10. **Validation** - Type-specific input validation

---

## 📝 Next Steps

1. Create migration and run: `php artisan migrate`
2. Run seeder: `php artisan db:seed --class=SettingsSeeder`
3. Create controller and views
4. Add routes to `web.php` and `api.php`
5. Add permissions to seeder
6. Implement admin interface with tabs
7. Add file upload for logo/images
8. Create API documentation
9. Add validation rules
10. Implement cache warming on boot
