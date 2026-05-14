# Settings System - Quick Start Guide

## ✅ Installation Complete!

All files have been created and the database has been seeded with default settings.

---

## 📁 Files Created

### Database
- ✅ `database/migrations/2025_11_27_050000_create_settings_table.php`
- ✅ `database/seeders/SettingsSeeder.php`

### Models & Helpers
- ✅ `app/Models/Setting.php`
- ✅ `app/Helpers/SettingsHelper.php` (auto-loaded)

### Controllers
- ✅ `app/Http/Controllers/Backend/SettingController.php` (Admin Panel)
- ✅ `app/Http/Controllers/Api/SettingController.php` (Public API)

### Views
- ✅ `resources/views/admin/settings/index.blade.php`

### Routes
- ✅ Web routes added to `routes/web.php`
- ✅ API routes added to `routes/api.php`

---

## 🔗 Available Routes

### Admin Panel (Web)
```
GET  /admin/settings              - View settings page
POST /admin/settings              - Update settings
POST /admin/settings/clear-cache  - Clear settings cache
```

**Permissions Required:** `setting_access`, `setting_edit`

### Public API
```
GET /api/settings                 - Get all public settings (grouped)
GET /api/settings/{group}         - Get settings by group
GET /api/setting/{key}            - Get specific setting value
```

---

## 💻 Usage Examples

### In Blade Templates
```blade
<!-- Simple usage -->
<title>{{ setting('site.name') }} - {{ setting('site.tagline') }}</title>
<img src="{{ setting('site.logo') }}" alt="Logo">
<p>Contact: {{ setting('contact.email') }}</p>

<!-- With default value -->
<p>Phone: {{ setting('contact.phone', '+855 12 000 000') }}</p>

<!-- Boolean check -->
@if(setting('mail.enabled'))
    <p>Email notifications are enabled</p>
@endif

<!-- Currency formatting -->
<p>{{ setting('currency.symbol') }}{{ number_format($price) }}</p>
```

### In Controllers
```php
use App\Models\Setting;

// Get single setting
$siteName = setting('site.name');
$maxImages = setting('property.max_images', 10);

// Get settings group
$localization = settings_group('localization');
// Returns: ['currency.default' => 'USD', 'currency.symbol' => '$', ...]

// Update setting
update_setting('site.name', 'New Site Name');
update_setting('property.max_images', 25, 'integer');

// Check if setting exists
if (has_setting('payment.enabled')) {
    // Process payment
}

// Delete setting
delete_setting('old.setting.key');

// Direct model usage
$allSettings = Setting::getAllSettings();
$publicSettings = Setting::getPublicSettings();
$propertySettings = Setting::getByGroup('property');
```

### Via JavaScript/API (Frontend)
```javascript
// Get all public settings
fetch('/api/settings')
  .then(r => r.json())
  .then(data => {
    console.log(data.settings.general['site.name']);
    console.log(data.settings.localization['currency.symbol']);
    // Output grouped by category
  });

// Get specific group
fetch('/api/settings/localization')
  .then(r => r.json())
  .then(data => {
    const currency = data.settings['currency.default']; // USD
    const symbol = data.settings['currency.symbol'];     // $
  });

// Get single setting
fetch('/api/setting/site.name')
  .then(r => r.json())
  .then(data => {
    console.log(data.value); // Panha Real Estate
  });
```

---

## 📋 Available Settings Categories

### 1. General (`general`)
- Site name, tagline, logo, favicon
- Contact information (email, phone, address)
- Social media links (Facebook, Instagram, Telegram)

### 2. Localization (`localization`)
- Currency settings (USD/KHR, exchange rate, symbol)
- Language settings (default, available languages)
- Timezone and date formats

### 3. Property (`property`)
- Listing duration, max images/videos
- Featured/premium pricing
- Auto-renew, moderation settings
- Min/max price limits

### 4. SEO (`seo`)
- Meta title, description, keywords
- Google Analytics ID
- Sitemap settings

### 5. Media (`media`)
- Upload size limits
- Allowed file extensions
- Image quality and thumbnail sizes

### 6. Map (`map`)
- Map provider (Google/Mapbox/Leaflet)
- API keys
- Default location (Phnom Penh coordinates)
- Default zoom level

### 7. Mail (`mail`)
- Enable/disable email notifications
- Sender name and address

### 8. Security (`security`)
- reCAPTCHA settings
- API rate limiting

---

## 🎯 Access Admin Panel

1. **Login as admin:**
   - Email: `admin@example.com` or `demo@realestate.com`
   - Password: `password`

2. **Navigate to Settings:**
   ```
   http://localhost:8000/admin/settings
   ```

3. **Features:**
   - ✅ Tabbed interface organized by category
   - ✅ Toggle switches for boolean values
   - ✅ Number inputs for integers/floats
   - ✅ Text areas for JSON data
   - ✅ File upload support for images
   - ✅ Tooltips with descriptions
   - ✅ Cache clearing button
   - ✅ Real-time validation

---

## 🔒 Permissions

The following permissions are already seeded:
- `setting_access` - Access Settings page
- `setting_edit` - Edit Settings
- `settings.view` - View Settings
- `settings.edit` - Edit Settings
- `settings.system` - System Settings
- `settings.backup` - Backup System

Assign these to roles via the Roles management page.

---

## 🚀 Adding New Settings

### Method 1: Via Admin Panel
1. Go to `/admin/settings`
2. Click the appropriate tab
3. Add/modify settings
4. Click "Save Settings"

### Method 2: Via Code
```php
use App\Models\Setting;

Setting::create([
    'key' => 'payment.stripe_key',
    'value' => 'sk_test_xxxxx',
    'type' => 'string',
    'group' => 'payment',
    'description' => 'Stripe Secret Key',
    'is_public' => false
]);

// Or use helper
update_setting('payment.stripe_key', 'sk_test_xxxxx', 'string');
```

### Method 3: Via Seeder
Add to `database/seeders/SettingsSeeder.php`:
```php
[
    'key' => 'your.new.setting',
    'value' => 'default value',
    'type' => 'string', // string, boolean, integer, float, json, file
    'group' => 'general',
    'description' => 'Description for admin panel',
    'is_public' => true // Accessible via API
],
```

Then run: `php artisan db:seed --class=SettingsSeeder`

---

## 🎨 Customizing the Admin View

Edit `resources/views/admin/settings/index.blade.php` to:
- Add custom input types
- Change tab icons
- Add file upload functionality
- Implement custom validation
- Add preview functionality

---

## 💡 Tips & Best Practices

1. **Caching:** Settings are automatically cached for 1 hour. Clear cache after updates.

2. **Public vs Private:** 
   - Set `is_public = true` for frontend-accessible settings
   - Set `is_public = false` for sensitive data (API keys, passwords)

3. **Type Safety:**
   - Use correct `type` field: string, boolean, integer, float, json, file
   - Boolean values automatically convert to true/false
   - JSON values automatically encode/decode

4. **Naming Convention:**
   - Use dot notation: `group.subgroup.setting`
   - Example: `payment.stripe.public_key`

5. **Performance:**
   - Settings are cached automatically
   - Use `setting()` helper for best performance
   - Clear cache when updating multiple settings

6. **Security:**
   - Never expose sensitive settings via public API
   - Use `is_public = false` for API keys, secrets
   - Validate all setting updates

---

## 🧪 Testing

```php
// Test in Tinker
php artisan tinker

>>> setting('site.name')
=> "Panha Real Estate"

>>> setting('currency.exchange_rate')
=> 4100

>>> setting('property.max_images')
=> 20

>>> settings_group('localization')
=> [
     "currency.default" => "USD",
     "currency.symbol" => "$",
     ...
   ]

>>> update_setting('site.name', 'My Real Estate')
>>> Cache::forget('app_settings')
>>> setting('site.name')
=> "My Real Estate"
```

---

## 📚 Additional Features

### Frontend Integration (React/Vue/Next.js)
```javascript
// Create a settings hook/composable
async function getSettings() {
  const response = await fetch('/api/settings');
  const { settings } = await response.json();
  return settings;
}

// Use in component
const settings = await getSettings();
document.title = settings.general['site.name'];
```

### Environment Override
You can override settings with environment variables:
```php
// In code
$apiKey = setting('payment.stripe_key') ?? env('STRIPE_KEY');
```

### Validation Rules
Add validation in `SettingController`:
```php
$rules = [
    'settings.property.max_images' => 'integer|min:1|max:100',
    'settings.contact.email' => 'email',
    'settings.currency.exchange_rate' => 'numeric|min:0',
];
```

---

## 🎉 You're All Set!

The Settings Management System is now fully functional and ready to use!

**Next Steps:**
1. Visit `/admin/settings` to configure your settings
2. Test the API endpoints
3. Use `setting()` helper in your code
4. Customize the admin interface if needed
5. Add more settings as your application grows

For questions or issues, refer to the main documentation or check the inline code comments.
