# Auto-Translate Feature Guide

## Overview

The Language File Translator now includes **Google Translate** integration for automatic translation of language files between English (EN), Khmer (KM), Chinese (ZH), and French (FR).

## Features

### 1. Bulk Auto-Translation
Translate all missing translations in a file at once:

- Click the **"Auto Translate"** button in the filter section
- Select **Source Language** (e.g., English)
- Select **Target Language** (e.g., Khmer)
- Select the **File** to translate (admin.php, common.php, or langauages.php)
- Click **"Start Translation"**

The system will:
- Find all keys that are missing or marked as `[NEEDS TRANSLATION]`
- Translate each key using Google Translate
- Save the translations automatically
- Show a summary of translated keys

### 2. Single Key Translation
Translate individual keys on-demand:

- Find the key you want to translate in the table
- Click the language button in the Action column:
  - **KM** button - Translate to Khmer
  - **ZH** button - Translate to Chinese
  - **FR** button - Translate to French
- Select the source language to translate from
- The translation will be applied automatically

## API Endpoints

### Auto-Translate Single Key
```
POST /admin/language-files/auto-translate
```

**Parameters:**
- `key` (required) - The translation key (e.g., 'menu.dashboard')
- `source_locale` (required) - Source language code (en, km, zh, fr)
- `target_locale` (required) - Target language code (en, km, zh, fr)
- `file` (required) - File name (admin, common, langauages)

**Response:**
```json
{
    "success": true,
    "message": "Translation completed successfully!",
    "translated_text": "ផ្ទាំងគ្រប់គ្រង"
}
```

### Auto-Translate Entire File
```
POST /admin/language-files/auto-translate-file
```

**Parameters:**
- `source_locale` (required) - Source language code
- `target_locale` (required) - Target language code
- `file` (required) - File name

**Response:**
```json
{
    "success": true,
    "message": "Successfully translated 45 keys",
    "translated_count": 45,
    "errors": []
}
```

## Language Code Mapping

The system uses the following Google Translate language codes:

| App Code | Google Code | Language |
|----------|-------------|----------|
| en       | en          | English  |
| km       | km          | Khmer    |
| zh       | zh-CN       | Chinese (Simplified) |
| fr       | fr          | French   |

## Rate Limiting

To avoid Google Translate API rate limiting:
- Bulk translation includes a **0.1 second delay** between each key
- For large files, translation may take several minutes
- Progress is shown in a loading dialog

## Error Handling

The system handles various error scenarios:

1. **Source/Target Same Language**: Shows warning message
2. **File Not Found**: Returns 404 error
3. **Missing Source Text**: Returns 404 error
4. **Translation API Error**: Shows specific error message
5. **Partial Failures**: Continues translating remaining keys and reports errors

## Best Practices

1. **Translate from English First**
   - Use English as your source language for best results
   - English has the most reliable translations to other languages

2. **Review Translations**
   - Auto-translations are not perfect
   - Always review and edit translations for accuracy
   - Consider cultural context and idioms

3. **Use Bulk Translation for Initial Setup**
   - Use bulk translation to get started quickly
   - Then refine individual translations manually

4. **Keep English Updated**
   - Maintain English as your primary language
   - Add new keys to English first
   - Then translate to other languages

## Technical Implementation

### Backend (Controller)
- `LanguageFileController::autoTranslate()` - Single key translation
- `LanguageFileController::autoTranslateFile()` - Bulk file translation
- Uses `stichoza/google-translate-php` package
- Implements retry logic and error handling

### Frontend (JavaScript)
- Auto-translate button triggers modal
- Individual language buttons for quick translation
- Loading indicators during translation
- Success/error feedback with SweetAlert2

### Routes
```php
Route::post('/auto-translate', [LanguageFileController::class, 'autoTranslate'])
    ->name('auto-translate');
    
Route::post('/auto-translate-file', [LanguageFileController::class, 'autoTranslateFile'])
    ->name('auto-translate-file');
```

## Dependencies

- **Package**: `stichoza/google-translate-php` v5.3+
- **Installation**: `composer require stichoza/google-translate-php`

## Troubleshooting

### Translation Not Working
1. Check internet connection (requires external API access)
2. Verify source text exists
3. Check Laravel logs: `storage/logs/laravel.log`

### Rate Limiting Errors
- Wait a few minutes before retrying
- Translate smaller batches
- Consider increasing delay between translations

### Incorrect Translations
- Review and edit manually
- Some phrases may not translate well automatically
- Consider context-specific translations

## Future Enhancements

Potential improvements:
- Cache translations to reduce API calls
- Support for additional languages
- Translation memory/glossary
- Professional translation service integration
- Translation quality scoring
- Batch translation progress indicator
