# Copilot Instructions - Panha Real Estate API

## Project Overview

This is a **Laravel 12** real estate API backend featuring property/project listings, multi-language support, role-based access control, and media management. Built for the Cambodia real estate market with bilingual support (English/Khmer).

**Tech Stack:** Laravel 12, PHP 8.2+, SQLite (dev), Laravel Sanctum, Yajra DataTables v12, Vite, TailwindCSS, AlpineJS

## Development Workflow

### Quick Start

```bash
# Setup (first time)
composer setup  # Runs install, .env copy, key generation, migrations, npm install/build

# Development (concurrent processes - runs 4 services)
composer dev    # Runs: php artisan serve + queue:listen + pail logs + npm run dev

# Testing
composer test   # Clears config cache and runs PHPUnit
```

### File Locations

- **API Controllers:** `app/Http/Controllers/Api/` (public REST API)
- **Backend Controllers:** `app/Http/Controllers/Backend/` (admin dashboard, extend `BaseController`)
- **API Routes:** `routes/api.php` (Sanctum auth)
- **Web Routes:** `routes/web.php` (admin routes: `admin.*` prefix, `role:admin,super_admin` middleware)
- **Models:** `app/Models/` (Eloquent with traits)
- **Traits:** `app/Traits/` (HasMedia, HasTranslations, HasMetaData, HasPermissions)
- **Helpers:** `app/Helpers/Helpers.php`, `SettingsHelper.php` (auto-loaded via composer)
- **Admin Views:** `resources/views/admin/` (Blade templates)
- **UI Libraries:** `public/assets/backend/` (custom vanilla JS components)

## Architecture Patterns

### 1. Trait-Based Model Extensions

Models use traits for cross-cutting concerns:

**HasTranslations** (`app/Traits/HasTranslations.php`)

- Polymorphic translation system via `Translation` model
- Define `protected $translatable = ['title', 'description'];` in model
- Usage: `$model->setTranslation('title', 'Value', 'km')` / `$model->getTranslation('title', 'km')`
- Auto-caching with 1-hour TTL, cleared on model save/delete
- Supports: completeness tracking, bulk operations, locale fallback to `defaultLocale`

**HasMedia** (`app/Traits/HasMedia.php`)

- Polymorphic media attachment via `EntityMedia` model
- Zone-based organization: `setMediaForZone($mediaId, 'gallery')` / `getMediaByZone('primary_image')`
- Methods: `syncMediaForZone()`, `clearZone()`, `getMediaUrlForZone()`, `hasMediaInZone()`
- Zones used: `primary_image` (featured), `gallery` (multiple images)

**HasPermissions** (`app/Traits/HasPermissions.php`)

- Custom RBAC implementation (not Laravel Permission package)
- Used by `User` model via `roles` relationship
- Check: `$user->hasRole('admin')`, `$user->hasPermission('user_create')`

### 2. DataTables Pattern (Yajra v12)

**All backend index pages use server-side DataTables:**

```php
// In Controller:
private function getDataTableData(Request $request)
{
    $query = User::with(['roles']); // Eager load relationships

    return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('custom', function ($model) {
            return '<span class="badge bg-info">'.$model->value.'</span>';
        })
        ->filterColumn('custom', function ($query, $keyword) {
            $query->whereHas('relation', fn($q) => $q->where('field', 'like', "%{$keyword}%"));
        })
        ->rawColumns(['custom', 'actions']) // CRITICAL: Allow HTML rendering
        ->make(true);
}

// Route handling:
if ($request->ajax()) {
    return $this->getDataTableData($request);
}
```

**Bootstrap 5 Badge Classes:** Use `bg-info`, `bg-success`, `bg-danger` (NOT `badge-*`)

### 3. Controller Hierarchy

**BaseController** (`app/Http/Controllers/Backend/BaseController.php`)

- All admin controllers extend `BaseController`
- Auto-applies resource-based permissions: `{resource}_access`, `{resource}_create`, `{resource}_edit`, `{resource}_delete`
- Define `protected string $resource = 'user';` in child controller
- Additional permissions: `protected array $additionalPermissions = ['dashboard_access'];`
- Custom method permissions: `$this->applyMethodPermission('user_verify', ['verify', 'unverify']);`

**Middleware Stack:**

- `auth` - Laravel Breeze authentication
- `role:admin,super_admin` - Custom `RoleMiddleware` checks roles
- `permission:user_create` - Custom `PermissionMiddleware` checks permissions

### 4. API Structure

**Public Routes:** No authentication required

```php
// Properties: /api/properties, /api/properties/{id}, /api/properties/featured
// Projects: /api/projects, /api/projects/{id}, /api/projects/high-yield
// News: /api/news, /api/news/latest, /api/news/categories
```

**Protected Routes:** `auth:sanctum` middleware

```php
// Favorites: POST /api/favorites, DELETE /api/favorites/{id}
// Property CRUD: POST/PUT/DELETE /api/properties/{id}
// User: GET /api/me, POST /api/logout
```

**Advanced Filtering:** Property/Project APIs support extensive query params:

- `listing_type`, `property_type`, `city`, `district`
- `min_price`, `max_price`, `min_bedrooms`, `bathrooms`
- `min_area`, `max_area`, `is_featured`, `search`
- `sort_by`, `sort_order`, `per_page`

Use Eloquent scopes in models: `scopeAvailable()`, `scopeFeatured()`, `scopePriceRange()`

## Conventions & Gotchas

### Translations

- **Storage:** `translations` table (polymorphic)
- **Service:** `TranslationService` provides bulk operations, stats, missing translation detection
- **Locales:** `en` (default), `km` (Khmer) - defined in models and service
- **Cache:** 1-hour TTL, auto-cleared on model update
- **Frontend:** Use `$model->translateTo('km')` to get locale-specific instance

### Media Management

- **Storage:** `public/storage/` (symlinked via `php artisan storage:link`)
- **Controller:** `MediaController` handles folder/file CRUD with bulk operations
- **Zones:** Logical grouping (e.g., `'featured_image'`, `'gallery'`, `'thumbnail'`)
- **Models:** `Media` (files), `EntityMedia` (pivot), use `HasMedia` trait

### Permissions

- **Naming:** `{resource}_{action}` (e.g., `user_create`, `role_edit`, `permission_delete`)
- **Special:** `dashboard_access`, `user_management_access`
- **Groups:** Permissions have `group` field (e.g., `'User Management'`, `'Content'`)
- **Status:** `status` boolean field (Active/Inactive) filterable in DataTables

### Helpers

- Auto-loaded via `composer.json`: `"files": ["app/Helpers/Helpers.php"]`
- `create_slug($string)` - URL-safe slugs
- `assetUrl()` - Dynamic asset URLs based on host
- `errorImageUrl()` - Default avatar fallback

### Seeders

- **Demo Data:** `PropertySeeder`, `ProjectSeeder`, `NewsArticleSeeder` (10 Cambodia properties)
- **RBAC Setup:** `CreateUserRolePermissionSeeder` creates roles/permissions/demo users
- **Demo User:** `demo@realestate.com` / `password`

## Common Tasks

### Adding a New Admin Resource

1. **Create Controller** extending `BaseController`:

```php
class VendorController extends BaseController
{
    protected string $resource = 'vendor';
    // Auto-applies: vendor_access, vendor_create, vendor_edit, vendor_delete
}
```

2. **Implement DataTables** in `index()`:

```php
private function getDataTableData(Request $request)
{
    $query = Vendor::with(['user', 'properties']);
    return DataTables::of($query)
        ->addColumn('actions', fn($v) => view('admin.vendors.actions', compact('v')))
        ->rawColumns(['actions'])
        ->make(true);
}
```

3. **Add Routes** in `routes/web.php`:

```php
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'role:admin']], function () {
    Route::resource('vendors', VendorController::class);
});
```

4. **Seed Permissions** in `CreateUserRolePermissionSeeder`:

```php
['title' => 'vendor_access', 'group' => 'Vendor Management'],
['title' => 'vendor_create', 'group' => 'Vendor Management'],
// ...
```

### Adding Translations

1. **Add trait to model**:

```php
use HasTranslations;
protected $translatable = ['title', 'description', 'features'];
```

2. **Save translations**:

```php
$property->setTranslation('title', 'ផ្ទះសម្រាប់លក់', 'km');
$property->setTranslations(['title' => '...', 'description' => '...'], 'km');
```

3. **Retrieve**:

```php
$property->getTranslation('title', 'km');           // Single field
$property->translateTo('km');                        // Full model copy with locale
$property->getTranslationCompleteness('km');         // % complete
```

### Adding Media Zones

1. **Define zone in model**:

```php
use HasMedia;

// Usage:
$property->setMediaForZone($mediaId, 'hero_image');        // Single
$property->syncMediaForZone([$id1, $id2], 'gallery');     // Multiple
$property->getMediaUrlForZone('hero_image', '/default.jpg');
```

2. **Display in view**:

```blade
<img src="{{ $property->getMediaUrlForZone('hero_image') }}">
@foreach($property->getMediaByZone('gallery') as $media)
    <img src="{{ $media->file->full_url }}">
@endforeach
```

## Testing & Debugging

- **Logs:** `storage/logs/laravel.log` or live via `php artisan pail`
- **Tinker:** `php artisan tinker` for model testing
- **DB:** SQLite database at `database/database.sqlite`
- **Queue:** Database driver, run `php artisan queue:work` for async jobs
- **API Testing:** Postman/Insomnia or README.md examples

## Custom UI Libraries (public/assets/backend/)

The admin panel uses **custom vanilla JS libraries** - not npm packages. Include via `<script src="{{ asset(...) }}">`.

### PanhaMediaUploadPreview - Image Upload Component

**File:** `filemanager/panha-media-upload-preview.js`

```javascript
const uploader = new PanhaMediaUploadPreview("#container", {
  maxFiles: 10,
  maxFileSize: 5 * 1024 * 1024, // 5MB
  fieldName: "gallery_images[]",
  gridColumns: 3,
  texts: { uploadText: "Click to select", addMoreText: "Add More" },
  theme: { primaryColor: "#0d6efd" },
});

// API Methods:
uploader.loadExistingFiles([{ url, name, id }]); // Pre-populate from server
uploader.getFiles(); // Get selected files
uploader.clear(); // Reset uploader
```

### PanhaMediaManagerModal - Media Library Modal

**File:** `filemanager/panha-media-manager-lib.js`  
**Component:** `resources/views/components/media-manager-modal.blade.php`

```blade
<x-media-manager-modal modalId="galleryModal" title="Select Images" :maxFiles="10" acceptedTypes="image/*" />
```

```javascript
window.PanhaMediaManagerModal.open({
  modalId: "galleryModal",
  defaultPath: "",
  onSelect: function (files) {
    files.forEach((f) => console.log(f.id, f.url, f.name));
  },
});
```

### panhaSelectVanilla - Multi-select Dropdown

**File:** `phanaSelect-Vanilla/panhaSelectVanilla.js`

```javascript
const select = panhaSelect("#features", {
  placeholder: "Select features...",
  multiple: true,
  allowClear: true,
  closeOnSelect: false,
});

select.clear(); // Clear all selections
select.selectOption("Pool"); // Programmatically select
select.getValue(); // Get selected values
```

### PanhaNoteEditor - WYSIWYG Rich Text Editor

**File:** `PanhaNote-Editor/panha-note-editor.bundle.js`

```javascript
document.addEventListener("panhaNoteEditorReady", function () {
  const editor = PanhaNoteEditor.init("#description-editor", {
    placeholder: "Enter description...",
    minHeight: "100px",
    toolbar: "full",
    onChange: (content) =>
      (document.getElementById("description").value = content),
  });

  editor.setContent(htmlContent); // Set content
  editor.getContent(); // Get HTML content
});
```

### Integrating Media Manager with Upload Preview

Pattern used in property/project forms - override click to open media library:

```javascript
setTimeout(() => {
  const uploadZone = document.querySelector("#uploader .pmup-upload-zone");
  const fileInput = document.querySelector("#uploader .pmup-file-input");
  fileInput.style.display = "none";
  fileInput.disabled = true;

  uploadZone.addEventListener(
    "click",
    function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      window.PanhaMediaManagerModal.open({
        modalId: "mediaModal",
        onSelect: (files) =>
          uploader.loadExistingFiles(
            files.map((f) => ({
              url: f.url,
              name: f.name,
              id: f.id,
              size: f.size || 0,
              type: "image/jpeg",
            }))
          ),
      });
      return false;
    },
    true
  );
}, 200);
```

## Additional Resources

- **Yajra DataTables Guide:** `YAJRA_DATATABLES_IMPLEMENTATION.md` (full examples)
- **Media Library Docs:** `public/assets/backend/filemanager/CLAUDE.md` (PanhaImagePreview library)
- **README:** API documentation with curl examples

## Important Notes

- **Never** use `badge-*` classes (Bootstrap 4), always `bg-*` (Bootstrap 5)
- **Always** eager load relationships in DataTables: `->with(['roles', 'permissions'])`
- **Always** add HTML columns to `->rawColumns([...])` in DataTables
- **Use** route names: `route('admin.users.index')` not hardcoded paths
- **Check** permissions via BaseController, not manual middleware calls
- **Cache** translations automatically, no manual cache management needed
- **Media zones:** Use `primary_image` for featured, `gallery` for multiple images
- **UI Libraries:** Always check `public/assets/backend/` before adding npm packages

## Settings & Lookup APIs

**Public Settings API:**

```php
GET /api/settings                    // All settings
GET /api/settings/{group}            // Settings by group (e.g., 'general', 'social')
GET /api/setting/{key}               // Single setting by key
```

**Lookup API (Public):**

```php
GET /api/lookups                      // All lookup data
GET /api/lookups/property-types       // Property types list
GET /api/lookups/features             // Property features
GET /api/lookups/features/grouped     // Features grouped by category
```

Used for dropdowns, filters, and configuration data on frontend.

- **Cache** translations automatically, no manual cache management needed
- **Media zones:** Use `primary_image` for featured, `gallery` for multiple images
- **UI Libraries:** Always check `public/assets/backend/` before adding npm packages
