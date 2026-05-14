# Language File Manager Documentation

## Overview

A complete management system for editing translation keys directly in the `resources/lang` directory files. This allows you to manage `admin.php`, `common.php`, and `langauages.php` files across all 4 languages (EN, KM, ZH, FR) through a web interface.

## Features

✅ **Direct File Editing** - Edit language files without touching code  
✅ **Multi-File Support** - Manage admin.php, common.php, langauages.php  
✅ **4 Languages** - English, Khmer, Chinese, French  
✅ **Dot Notation** - Use nested keys (e.g., `menu.dashboard`)  
✅ **Key Sync** - Auto-sync missing keys across languages  
✅ **DataTables Interface** - Server-side pagination and search  
✅ **Real-time Filtering** - Filter by language, file, and key name  

## File Structure

```
app/Http/Controllers/Backend/
└── LanguageFileController.php    # CRUD operations for language files

resources/
├── views/admin/language-files/
│   └── index.blade.php            # DataTable interface
└── lang/
    ├── en/
    │   ├── admin.php
    │   ├── common.php
    │   └── langauages.php
    ├── km/ [same files]
    ├── zh/ [same files]
    └── fr/ [same files]
```

## Routes

```php
GET    /admin/language-files           - Index page with DataTable
POST   /admin/language-files           - Create new key
POST   /admin/language-files/update    - Update existing key
DELETE /admin/language-files/destroy   - Delete key
POST   /admin/language-files/sync      - Sync keys across languages
```

## Permissions

```php
'language_file_access' => 'Access Language Files',
'language_file_create' => 'Create Language Keys',
'language_file_edit'   => 'Edit Language Keys',
'language_file_delete' => 'Delete Language Keys',
'language_file_sync'   => 'Sync Language Keys',
```

## Usage

### 1. Access Language Files Manager

Navigate to: **Content Management → Language Files**  
URL: `http://localhost:8000/admin/language-files`

### 2. View Translation Keys

**Default View:** English language, common.php file

**Filter Options:**
- Language: EN / KM / ZH / FR
- File: admin.php / common.php / langauages.php
- Search Key: Type to filter by key name

### 3. Add New Translation Key

Click **"Add New Key"** button

```
Language:  English (EN)
File:      admin.php
Key:       menu.new_section       (use dot notation)
Value:     New Section
```

**Key Naming:**
- Top-level: `dashboard`, `welcome`
- Nested: `menu.dashboard`, `users.create`, `forms.validation.required`

### 4. Edit Translation Key

Click **Edit** button (blue pencil icon) on any row

- Key field is read-only during edit
- Only value can be changed

### 5. Delete Translation Key

Click **Delete** button (red trash icon)

⚠️ **Warning:** This deletes the key from the PHP file permanently.

### 6. Sync Keys Across Languages

**Use Case:** You added new keys to English but need them in other languages

**Steps:**
1. Click **"Sync Keys"** button
2. Select:
   - **Source Language:** en (the complete one)
   - **Target Languages:** km, zh, fr (select multiple)
   - **File:** admin.php
3. Click **"Sync Now"**

**Result:**
- Missing keys are added to target files
- Placeholder value: `[NEEDS TRANSLATION]`
- Report shows how many keys added per language

**Example Output:**
```
Sync Report:
- KM: 5 keys added
- ZH: 3 keys added
- FR: 7 keys added
```

### 7. Search and Filter

**Search by Key Name:**
```
Input: "menu"
Results: menu.dashboard, menu.users, menu.settings, etc.
```

**Filter by Language + File:**
```
Language: Khmer (KM)
File: admin.php
Results: All keys from resources/lang/km/admin.php
```

## Key Features Explained

### Dot Notation Support

The system handles nested arrays automatically:

**PHP File Structure:**
```php
return [
    'menu' => [
        'dashboard' => 'Dashboard',
        'users' => 'Users',
    ],
    'welcome' => 'Welcome'
];
```

**In Manager:**
- Key: `menu.dashboard` → Value: `Dashboard`
- Key: `menu.users` → Value: `Users`
- Key: `welcome` → Value: `Welcome`

### File Structure Preservation

The controller uses `varExport()` to maintain proper PHP array formatting:

```php
// Before
return [
    'item' => 'value',
];

// After adding new key (maintains formatting)
return [
    'item' => 'value',
    'new_item' => 'new value',
];
```

### Smart Key Sync

**Scenario:** English has 50 keys, Khmer only has 35

**Sync Process:**
1. Loads English keys: 50 total
2. Loads Khmer keys: 35 total
3. Finds missing: 15 keys
4. Adds to Khmer with `[NEEDS TRANSLATION]` placeholder
5. Saves Khmer file with all 50 keys

**Then:** Translator can search for `[NEEDS TRANSLATION]` to find what needs translating

## DataTable Configuration

```javascript
pageLength: 50,           // Show 50 rows (lots of keys)
order: [[2, 'asc']],     // Sort by Key column (alphabetical)
serverSide: true,        // Handle large files efficiently
```

**Columns:**
1. # - Row number
2. Language - Badge (color-coded)
3. Key - Code formatted (e.g., `menu.dashboard`)
4. Value - Truncated if long
5. Actions - Edit/Delete buttons

## API Responses

### Success Response
```json
{
  "success": true,
  "message": "Translation updated successfully!"
}
```

### Sync Response
```json
{
  "success": true,
  "message": "Translations synced successfully!",
  "report": {
    "km": 5,
    "zh": 3,
    "fr": 7
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Translation key already exists!"
}
```

## Common Translation Keys

### admin.php
```php
'menu' => [
    'dashboard' => 'Dashboard',
    'users' => 'Users',
    'properties' => 'Properties',
    // ... etc
],
'users' => [
    'title' => 'Users Management',
    'create' => 'Add New User',
    // ... etc
]
```

### common.php
```php
'save' => 'Save',
'cancel' => 'Cancel',
'delete' => 'Delete',
'edit' => 'Edit',
'success' => 'Success',
'error' => 'Error',
// ... etc
```

### langauages.php
```php
'en' => 'English',
'km' => 'Khmer',
'zh' => 'Chinese',
'fr' => 'French',
```

## Best Practices

### 1. Use Consistent Naming
```
✅ Good: menu.dashboard, menu.users, menu.settings
❌ Bad: dashboard_menu, menuUsers, menu-settings
```

### 2. Group Related Keys
```php
'users' => [
    'title' => '...',
    'create' => '...',
    'edit' => '...',
    'delete' => '...',
]
```

### 3. Start with English
1. Add all keys to English first
2. Test in application
3. Use Sync feature to propagate to other languages
4. Translate `[NEEDS TRANSLATION]` placeholders

### 4. Keep Values Clear
```
✅ Good: 'Delete User'
❌ Bad: 'Are you sure you want to permanently delete this user from the system?'
     (too long, use in blade file instead)
```

## Troubleshooting

### Issue: Key not appearing in app
**Solution:** Clear Laravel cache: `php artisan cache:clear`

### Issue: "Key already exists" error
**Solution:** Use Edit instead of Add New

### Issue: File permission error
**Solution:** Ensure `resources/lang/` is writable:
```bash
chmod -R 775 resources/lang/
```

### Issue: Sync not adding all keys
**Solution:** Check that source file has the keys you expect. Sync only adds missing keys, doesn't overwrite existing ones.

### Issue: Special characters broken
**Solution:** The controller handles escaping. Make sure your PHP files have UTF-8 encoding.

## Security

- ✅ CSRF protection on all POST/DELETE routes
- ✅ BaseController applies permission checks
- ✅ Input validation on all operations
- ✅ File path sanitization (only allows specific files)
- ✅ No arbitrary file writes

## Performance

- Server-side DataTables for large key sets
- File reads cached by Laravel's translation system
- Efficient array flattening algorithm
- Minimal database queries (permissions only)

## Integration with Translation System

Language files managed here are used throughout the app:

**In Blade:**
```blade
{{ __('admin.menu.dashboard') }}
{{ __('common.save') }}
```

**In Controllers:**
```php
__('admin.users.title')
__('common.success')
```

**In JavaScript:**
```blade
<script>
const successMsg = '{{ __("common.success") }}';
</script>
```

## Workflow Example

**Task:** Add new "Reports" section to menu in all languages

**Steps:**

1. **Add to English:**
   - Language: EN
   - File: admin.php
   - Key: `menu.reports`
   - Value: `Reports`

2. **Sync to Other Languages:**
   - Click "Sync Keys"
   - Source: EN
   - Targets: KM, ZH, FR
   - File: admin.php
   - Click "Sync Now"

3. **Translate Placeholders:**
   - Filter: Language=KM, File=admin.php
   - Search: "[NEEDS TRANSLATION]"
   - Edit `menu.reports`: Change to `របាយការណ៍`
   - Repeat for ZH and FR

4. **Test in App:**
   - Switch language in UI
   - Verify "Reports" appears in all languages

---

**Created:** November 29, 2025  
**Laravel Version:** 12.38.1  
**Files Managed:** resources/lang/{locale}/{file}.php  
**Languages Supported:** EN, KM, ZH, FR
