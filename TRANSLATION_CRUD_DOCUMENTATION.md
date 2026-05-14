# Multi-Language CRUD System with Inline Editing

## Overview

A complete translation management system with **jQuery AJAX inline editing** and **DataTables** server-side processing. Manage all translations for Property, Project, and NewsArticle models across 4 languages (EN, KM, ZH, FR).

## Features

✅ **Inline Table Editing** - Click any value cell to edit directly in the table (Ctrl+Enter to save, Escape to cancel)  
✅ **Modal CRUD Forms** - Create and edit translations with validation  
✅ **Real-time Filtering** - Filter by language, model type, and field name  
✅ **Server-side DataTables** - Efficient pagination and search  
✅ **Multi-language Support** - Interface translated to 4 languages  
✅ **Auto Cache Clearing** - Translations cached with automatic invalidation  
✅ **Polymorphic Relations** - Works with any translatable model  

## File Structure

```
app/
├── Models/
│   └── Translation.php                    # Translation model with scopes
├── Http/Controllers/Backend/
│   └── TranslationController.php          # CRUD + inline editing
database/
└── seeders/
    └── CreateUserRolePermissionSeeder.php # Translation permissions
resources/
├── views/admin/translations/
│   └── index.blade.php                    # DataTable with inline edit
└── lang/
    ├── en/admin.php                       # English translations
    ├── km/admin.php                       # Khmer translations
    ├── zh/admin.php                       # Chinese translations
    └── fr/admin.php                       # French translations
routes/
└── web.php                                # Translation routes
```

## Routes

```php
// All routes use admin prefix and require authentication
GET    /admin/translations              - Index page with DataTable
POST   /admin/translations              - Store new translation
PUT    /admin/translations/{id}         - Update translation (modal form)
PUT    /admin/translations/{id}/inline  - Update single field (inline edit)
DELETE /admin/translations/{id}         - Delete translation
POST   /admin/translations/bulk-destroy - Delete multiple translations
```

## Permissions

```php
'translation_access'  => 'Access Translations',
'translation_create'  => 'Create Translations',
'translation_edit'    => 'Edit Translations',
'translation_delete'  => 'Delete Translations',
'translation_export'  => 'Export Translations',
```

Assigned to roles via BaseController's resource-based permission system.

## Database Schema

**translations** table (already exists):
```sql
id                  BIGINT UNSIGNED PRIMARY KEY
translatable_type   VARCHAR(255)      -- Model class name
translatable_id     BIGINT UNSIGNED   -- Model ID
locale              VARCHAR(2)        -- Language code (en, km, zh, fr)
field               VARCHAR(255)      -- Field name (title, description, etc.)
value               LONGTEXT          -- Translation value
created_at          TIMESTAMP
updated_at          TIMESTAMP

UNIQUE KEY: (translatable_type, translatable_id, locale, field)
INDEX: (translatable_type, translatable_id)
INDEX: (locale)
```

## Usage

### 1. Access Translations Page

Navigate to: **Content Management → Translations**  
URL: `http://localhost:8000/admin/translations`

### 2. Create New Translation

**Button:** "Add New Translation"

```
Model Type:    Property / Project / News Article
Model ID:      1, 2, 3, etc.
Language:      English / ខ្មែរ / 中文 / Français
Field:         title, description, features, etc.
Value:         [Translation text]
```

### 3. Inline Editing

1. Click any **Value** cell in the table
2. Edit the text in the textarea
3. Press **Ctrl+Enter** to save OR click outside to blur-save
4. Press **Escape** to cancel

**Visual Feedback:**
- Hover: Light gray background with edit icon
- Editing: Blue 2px border around textarea
- Success: Toast notification appears

### 4. Filter Translations

```
Filter by Language:    All / English / Khmer / Chinese / French
Filter by Model:       All / Property / Project / News Article
Filter by Field:       Search field name (e.g., "title")
```

Click **Filter** to apply or **Clear** to reset.

### 5. Delete Translation

Click the **red trash icon** → Confirm in SweetAlert2 dialog.

## API Responses

### Success Response
```json
{
  "success": true,
  "message": "Translation updated successfully!",
  "data": {
    "id": 123,
    "translatable_type": "App\\Models\\Property",
    "translatable_id": 5,
    "locale": "km",
    "field": "title",
    "value": "ផ្ទះសម្រាប់លក់",
    "created_at": "2025-11-29T10:30:00.000000Z",
    "updated_at": "2025-11-29T11:45:00.000000Z"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Failed to update translation: Validation error"
}
```

## JavaScript Inline Editing Logic

### Key Variables
```javascript
let table;              // DataTables instance
let editingCell = null; // Currently editing cell reference
```

### Event Flow

1. **Click Cell** → Convert to textarea
2. **Focus** → Select all text
3. **Blur Event** → AJAX save via PUT /translations/{id}/inline
4. **Success** → Update cell with new value preview
5. **Error** → Revert cell and show error alert

### Keyboard Shortcuts
- **Ctrl+Enter**: Save changes
- **Escape**: Cancel editing

## Translation Model Methods

```php
// Scopes
Translation::locale('km')->get();              // Filter by language
Translation::translatableType(Property::class)->get(); // Filter by model
Translation::field('title')->get();            // Filter by field

// Static Helpers
Translation::getAvailableLocales();            // ['en', 'km', 'zh', 'fr']
Translation::getLocaleDisplayName('km');       // 'ខ្មែរ (Khmer)'
Translation::getTranslatableModels();          // [Property, Project, NewsArticle]
```

## Cache Management

**Automatic Cache Clearing:**
- On translation save: `translation:{type}:{id}` key cleared
- On translation delete: All translation cache flushed
- Cache TTL: 1 hour (defined in HasTranslations trait)

**Manual Cache Clear:**
```php
Cache::forget("translation:App\\Models\\Property:5");
Cache::tags(['translations'])->flush();
```

## DataTable Configuration

```javascript
processing: true,           // Show loading spinner
serverSide: true,          // Server-side processing
pageLength: 25,            // 25 rows per page
order: [[0, 'desc']],     // Sort by ID descending
searchDelay: 350,         // Debounce search input
```

**Columns:**
- ID
- Model Type (with class-to-name mapping)
- Model ID (with # prefix)
- Locale (color-coded badges: EN=blue, KM=green, ZH=red, FR=cyan)
- Field
- Value (truncated to 50 chars with full-text tooltip)
- Actions (Edit and Delete buttons)

## Styling Features

### Editable Cell Hover Effect
```css
.editable-cell:hover {
    background-color: #f8f9fa;
}
.editable-cell:hover::after {
    content: '\f044';  /* Font Awesome edit icon */
    position: absolute;
    right: 8px;
}
```

### Editing State
```css
.inline-edit-input {
    border: 2px solid #007bff;
    min-height: 60px;
}
```

### Locale Badges
```html
<span class="badge bg-primary">English</span>    <!-- Blue -->
<span class="badge bg-success">ខ្មែរ</span>      <!-- Green -->
<span class="badge bg-danger">中文</span>         <!-- Red -->
<span class="badge bg-info">Français</span>      <!-- Cyan -->
```

## Integration with Existing Models

All models using `HasTranslations` trait automatically work:

```php
// Property Model Example
protected $translatable = ['title', 'description', 'features'];

// Create translation via model
$property->setTranslation('title', 'ផ្ទះទំនើប', 'km');

// Or manage via Translations CRUD interface
// → Go to /admin/translations
// → Create: Property, ID=5, km, title, 'ផ្ទះទំនើប'
```

## Translation Keys Used

### English (en/admin.php)
```php
'translations' => [
    'title' => 'Translations Management',
    'subtitle' => 'Manage multi-language translations for all content',
    'add_new' => 'Add New Translation',
    'edit' => 'Edit Translation',
    'model_type' => 'Model Type',
    'locale' => 'Language',
    'field' => 'Field',
    'value' => 'Value',
    'filter_locale' => 'Filter by Language',
    'delete_confirm' => 'This translation will be permanently deleted!',
]
```

### Common Keys (common.php)
```php
'success' => 'Success',
'error' => 'Error',
'delete' => 'Delete',
'cancel' => 'Cancel',
'save' => 'Save',
'loading' => 'Loading',
'confirm_delete' => 'Are you sure?',
```

## Testing Checklist

- [ ] Access /admin/translations (requires translation_access permission)
- [ ] Create new translation via modal form
- [ ] Inline edit by clicking value cell
- [ ] Save inline edit with Ctrl+Enter
- [ ] Cancel inline edit with Escape
- [ ] Filter by language, model, and field
- [ ] Delete single translation
- [ ] Bulk delete multiple translations
- [ ] Test with all 4 language interfaces
- [ ] Verify cache clearing on update
- [ ] Check DataTable pagination and search

## Browser Console Commands

```javascript
// Get current DataTable data
table.data();

// Reload table without resetting pagination
table.ajax.reload(null, false);

// Check if cell is being edited
console.log(editingCell);

// Manually trigger save
saveInlineEdit($('.inline-edit-input'));
```

## Troubleshooting

### Issue: Inline edit not saving
**Solution:** Check browser console for AJAX errors. Verify CSRF token is present.

### Issue: DataTable not loading
**Solution:** Check that route returns JSON with `DataTables::of($query)->make(true)`

### Issue: Permissions error
**Solution:** Run seeder: `php artisan db:seed --class=CreateUserRolePermissionSeeder`

### Issue: Translation not showing in frontend
**Solution:** Clear cache: `php artisan cache:clear`

## Security Notes

- ✅ CSRF protection on all POST/PUT/DELETE routes
- ✅ BaseController applies resource-based permissions
- ✅ XSS prevention via htmlspecialchars() on output
- ✅ SQL injection prevention via Eloquent ORM
- ✅ Authorization middleware on admin routes

## Performance Optimization

- Server-side DataTables reduces client-side load
- Eager loading with `with('translatable')` prevents N+1 queries
- Cache layer with 1-hour TTL reduces database hits
- Indexed database columns for fast filtering

## Future Enhancements

- [ ] Export translations to Excel/CSV
- [ ] Import translations from file
- [ ] Translation history/versioning
- [ ] Bulk edit multiple translations
- [ ] Translation completeness dashboard
- [ ] Auto-translate with API integration
- [ ] Compare translations side-by-side

---

**Created:** November 29, 2025  
**Laravel Version:** 12.38.1  
**PHP Version:** 8.2.21  
**Database:** SQLite (Development)
