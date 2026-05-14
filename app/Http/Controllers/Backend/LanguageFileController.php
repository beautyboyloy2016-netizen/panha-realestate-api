<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Yajra\DataTables\DataTables;
use Stichoza\GoogleTranslate\GoogleTranslate;

class LanguageFileController extends BaseController
{
    protected string $resource = 'language_file';

    protected $langPath;
    protected $availableLocales = ['en', 'km', 'zh', 'fr'];
    protected $availableFiles = ['admin', 'common', 'langauages'];
    protected $localeMapping = [
        'en' => 'en',
        'km' => 'km',
        'zh' => 'zh-CN',
        'fr' => 'fr'
    ];

    public function __construct()
    {
        parent::__construct();
        $this->langPath = resource_path('lang');
    }

    /**
     * Display a listing of translation keys.
     */
    public function index(Request $request)
    {
        if ($request->ajax() && $request->has('ajax')) {
            return $this->getMultiLanguageData($request);
        }

        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $locales = $this->availableLocales;
        $files = $this->availableFiles;

        return view('admin.language-files.index', compact('locales', 'files'));
    }

    /**
     * Get multi-language data for the new translator interface
     */
    private function getMultiLanguageData(Request $request)
    {
        $file = $request->get('file', 'admin');
        $searchKey = $request->get('search_key', '');

        // Load translations for all languages
        $allTranslations = [];
        foreach ($this->availableLocales as $locale) {
            $translations = $this->loadTranslations($locale, $file);
            $allTranslations[$locale] = $this->flattenArray($translations);
        }

        // Get all unique keys from all languages
        $allKeys = [];
        foreach ($allTranslations as $locale => $translations) {
            $allKeys = array_merge($allKeys, array_keys($translations));
        }
        $allKeys = array_unique($allKeys);
        sort($allKeys);

        // Filter by search key if provided
        if (!empty($searchKey)) {
            $allKeys = array_filter($allKeys, function ($key) use ($searchKey) {
                return stripos($key, $searchKey) !== false;
            });
        }

        // Build the multi-language data structure
        $data = [];
        foreach ($allKeys as $key) {
            $item = ['key' => $key];

            foreach ($this->availableLocales as $locale) {
                $item[$locale] = $allTranslations[$locale][$key] ?? null;
            }

            $data[] = $item;
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get DataTable data
     */
    private function getDataTableData(Request $request)
    {
        $locale = $request->get('locale', 'en');
        $file = $request->get('file', 'common');
        $searchKey = $request->get('search_key', '');

        $translations = $this->loadTranslations($locale, $file);
        $flatTranslations = $this->flattenArray($translations);

        // Filter by search key
        if (!empty($searchKey)) {
            $flatTranslations = array_filter($flatTranslations, function ($key) use ($searchKey) {
                return stripos($key, $searchKey) !== false;
            }, ARRAY_FILTER_USE_KEY);
        }

        $data = [];
        foreach ($flatTranslations as $key => $value) {
            $data[] = [
                'key' => $key,
                'value' => $value,
                'locale' => $locale,
                'file' => $file,
            ];
        }

        return DataTables::of(collect($data))
            ->addIndexColumn()
            ->addColumn('key_display', function ($row) {
                return '<code class="text-primary">' . htmlspecialchars($row['key']) . '</code>';
            })
            ->addColumn('value_display', function ($row) {
                $value = $row['value'] ?? '';
                $preview = str($value)->limit(80);
                return '<span class="editable-value" data-key="' . htmlspecialchars($row['key']) . '"
                        title="' . htmlspecialchars($value) . '">' .
                       htmlspecialchars($preview) .
                       '</span>';
            })
            ->addColumn('locale_badge', function ($row) {
                $colors = [
                    'en' => 'primary',
                    'km' => 'success',
                    'zh' => 'danger',
                    'fr' => 'info'
                ];
                $color = $colors[$row['locale']] ?? 'secondary';
                $names = [
                    'en' => 'EN',
                    'km' => 'KM',
                    'zh' => 'ZH',
                    'fr' => 'FR'
                ];
                return '<span class="badge bg-' . $color . '">' . $names[$row['locale']] . '</span>';
            })
            ->addColumn('actions', function ($row) {
                return '<button class="btn btn-sm btn-primary edit-key"
                            data-key="' . htmlspecialchars($row['key']) . '"
                            data-value="' . htmlspecialchars($row['value']) . '"
                            data-locale="' . $row['locale'] . '"
                            data-file="' . $row['file'] . '">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-key"
                            data-key="' . htmlspecialchars($row['key']) . '"
                            data-locale="' . $row['locale'] . '"
                            data-file="' . $row['file'] . '">
                            <i class="fas fa-trash"></i>
                        </button>';
            })
            ->rawColumns(['key_display', 'value_display', 'locale_badge', 'actions'])
            ->make(true);
    }

    /**
     * Update translation key
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,km,zh,fr',
            'file' => 'required|string|in:admin,common,langauages',
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        try {
            $filePath = $this->langPath . '/' . $validated['locale'] . '/' . $validated['file'] . '.php';

            if (!File::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Language file not found!'
                ], 404);
            }

            $translations = include $filePath;
            $this->setNestedValue($translations, $validated['key'], $validated['value']);

            $content = "<?php\n\nreturn " . $this->varExport($translations) . ";\n";
            File::put($filePath, $content);

            return response()->json([
                'success' => true,
                'message' => 'Translation updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store new translation key
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,km,zh,fr',
            'file' => 'required|string|in:admin,common,langauages',
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        try {
            $filePath = $this->langPath . '/' . $validated['locale'] . '/' . $validated['file'] . '.php';

            if (!File::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Language file not found!'
                ], 404);
            }

            $translations = include $filePath;

            // Check if key already exists
            if ($this->getNestedValue($translations, $validated['key']) !== null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Translation key already exists!'
                ], 422);
            }

            $this->setNestedValue($translations, $validated['key'], $validated['value']);

            $content = "<?php\n\nreturn " . $this->varExport($translations) . ";\n";
            File::put($filePath, $content);

            return response()->json([
                'success' => true,
                'message' => 'Translation created successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete translation key
     */
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'locale' => 'required|string|in:en,km,zh,fr',
            'file' => 'required|string|in:admin,common,langauages',
            'key' => 'required|string',
        ]);

        try {
            $filePath = $this->langPath . '/' . $validated['locale'] . '/' . $validated['file'] . '.php';

            if (!File::exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Language file not found!'
                ], 404);
            }

            $translations = include $filePath;
            $this->unsetNestedValue($translations, $validated['key']);

            $content = "<?php\n\nreturn " . $this->varExport($translations) . ";\n";
            File::put($filePath, $content);

            return response()->json([
                'success' => true,
                'message' => 'Translation deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync translations across all languages
     */
    public function sync(Request $request)
    {
        $validated = $request->validate([
            'source_locale' => 'required|string|in:en,km,zh,fr',
            'target_locales' => 'required|array',
            'target_locales.*' => 'required|string|in:en,km,zh,fr',
            'file' => 'required|string|in:admin,common,langauages',
        ]);

        try {
            $sourceFile = $this->langPath . '/' . $validated['source_locale'] . '/' . $validated['file'] . '.php';
            $sourceTranslations = include $sourceFile;
            $sourceKeys = array_keys($this->flattenArray($sourceTranslations));

            $report = [];

            foreach ($validated['target_locales'] as $targetLocale) {
                if ($targetLocale === $validated['source_locale']) continue;

                $targetFile = $this->langPath . '/' . $targetLocale . '/' . $validated['file'] . '.php';
                $targetTranslations = include $targetFile;
                $targetFlat = $this->flattenArray($targetTranslations);

                $missingKeys = array_diff($sourceKeys, array_keys($targetFlat));

                foreach ($missingKeys as $key) {
                    $this->setNestedValue($targetTranslations, $key, '[NEEDS TRANSLATION]');
                }

                $content = "<?php\n\nreturn " . $this->varExport($targetTranslations) . ";\n";
                File::put($targetFile, $content);

                $report[$targetLocale] = count($missingKeys);
            }

            return response()->json([
                'success' => true,
                'message' => 'Translations synced successfully!',
                'report' => $report
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync translations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-translate a single key using Google Translate
     */
    public function autoTranslate(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'source_locale' => 'required|string|in:en,km,zh,fr',
            'target_locale' => 'required|string|in:en,km,zh,fr',
            'file' => 'required|string|in:admin,common,langauages',
        ]);

        try {
            // Get source text
            $sourceFile = $this->langPath . '/' . $validated['source_locale'] . '/' . $validated['file'] . '.php';
            if (!File::exists($sourceFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source language file not found!'
                ], 404);
            }

            $sourceTranslations = include $sourceFile;
            $sourceText = $this->getNestedValue($sourceTranslations, $validated['key']);

            if (empty($sourceText)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source text not found for key: ' . $validated['key']
                ], 404);
            }

            // Translate using Google Translate
            $translator = new GoogleTranslate();
            $translator->setSource($this->localeMapping[$validated['source_locale']]);
            $translator->setTarget($this->localeMapping[$validated['target_locale']]);

            $translatedText = $translator->translate($sourceText);

            // Save to target file
            $targetFile = $this->langPath . '/' . $validated['target_locale'] . '/' . $validated['file'] . '.php';
            if (!File::exists($targetFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target language file not found!'
                ], 404);
            }

            $targetTranslations = include $targetFile;
            $this->setNestedValue($targetTranslations, $validated['key'], $translatedText);

            $content = "<?php\n\nreturn " . $this->varExport($targetTranslations) . ";\n";
            File::put($targetFile, $content);

            return response()->json([
                'success' => true,
                'message' => 'Translation completed successfully!',
                'translated_text' => $translatedText
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to translate: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-translate all missing keys in a file
     */
    public function autoTranslateFile(Request $request)
    {
        $validated = $request->validate([
            'source_locale' => 'required|string|in:en,km,zh,fr',
            'target_locale' => 'required|string|in:en,km,zh,fr',
            'file' => 'required|string|in:admin,common,langauages',
        ]);

        try {
            if ($validated['source_locale'] === $validated['target_locale']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source and target locales cannot be the same!'
                ], 422);
            }

            // Load source file
            $sourceFile = $this->langPath . '/' . $validated['source_locale'] . '/' . $validated['file'] . '.php';
            if (!File::exists($sourceFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Source language file not found!'
                ], 404);
            }

            $sourceTranslations = include $sourceFile;
            $sourceFlat = $this->flattenArray($sourceTranslations);

            // Load target file
            $targetFile = $this->langPath . '/' . $validated['target_locale'] . '/' . $validated['file'] . '.php';
            if (!File::exists($targetFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Target language file not found!'
                ], 404);
            }

            $targetTranslations = include $targetFile;
            $targetFlat = $this->flattenArray($targetTranslations);

            // Find keys that need translation (empty or missing)
            $keysToTranslate = [];
            foreach ($sourceFlat as $key => $value) {
                if (empty($targetFlat[$key]) || $targetFlat[$key] === '[NEEDS TRANSLATION]') {
                    $keysToTranslate[$key] = $value;
                }
            }

            if (empty($keysToTranslate)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No translations needed. All keys are already translated!',
                    'translated_count' => 0
                ]);
            }

            // Initialize Google Translate
            $translator = new GoogleTranslate();
            $translator->setSource($this->localeMapping[$validated['source_locale']]);
            $translator->setTarget($this->localeMapping[$validated['target_locale']]);

            $translatedCount = 0;
            $errors = [];

            foreach ($keysToTranslate as $key => $sourceText) {
                try {
                    $translatedText = $translator->translate($sourceText);
                    $this->setNestedValue($targetTranslations, $key, $translatedText);
                    $translatedCount++;

                    // Small delay to avoid rate limiting
                    usleep(100000); // 0.1 second delay
                } catch (\Exception $e) {
                    $errors[] = "Failed to translate key '{$key}': " . $e->getMessage();
                }
            }

            // Save the updated target file
            $content = "<?php\n\nreturn " . $this->varExport($targetTranslations) . ";\n";
            File::put($targetFile, $content);

            $message = "Successfully translated {$translatedCount} keys";
            if (!empty($errors)) {
                $message .= " with " . count($errors) . " errors";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'translated_count' => $translatedCount,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to auto-translate file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Load translations from file
     */
    private function loadTranslations($locale, $file)
    {
        $filePath = $this->langPath . '/' . $locale . '/' . $file . '.php';

        if (!File::exists($filePath)) {
            return [];
        }

        return include $filePath;
    }

    /**
     * Flatten nested array with dot notation
     */
    private function flattenArray($array, $prefix = '')
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '.' . $key : $key;

            if (is_array($value)) {
                $result = array_merge($result, $this->flattenArray($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Get nested value using dot notation
     */
    private function getNestedValue($array, $key)
    {
        $keys = explode('.', $key);

        foreach ($keys as $k) {
            if (!isset($array[$k])) {
                return null;
            }
            $array = $array[$k];
        }

        return $array;
    }

    /**
     * Set nested value using dot notation
     */
    private function setNestedValue(&$array, $key, $value)
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k]) || !is_array($current[$k])) {
                $current[$k] = [];
            }
            $current = &$current[$k];
        }

        $current = $value;
    }

    /**
     * Unset nested value using dot notation
     */
    private function unsetNestedValue(&$array, $key)
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $current = &$array;

        foreach ($keys as $k) {
            if (!isset($current[$k])) {
                return;
            }
            $current = &$current[$k];
        }

        unset($current[$lastKey]);
    }

    /**
     * Export array with proper formatting
     */
    private function varExport($array, $indent = 0)
    {
        $spaces = str_repeat('    ', $indent);
        $output = "[\n";

        foreach ($array as $key => $value) {
            $output .= $spaces . '    ';
            $output .= is_string($key) ? "'{$key}'" : $key;
            $output .= ' => ';

            if (is_array($value)) {
                $output .= $this->varExport($value, $indent + 1);
            } else {
                $escaped = str_replace(["\\", "'"], ["\\\\", "\\'"], $value);
                $output .= "'{$escaped}'";
            }

            $output .= ",\n";
        }

        $output .= $spaces . ']';

        return $output;
    }
}
