<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class TranslationService
{
    /**
     * Default locale
     */
    protected string $defaultLocale = 'en';

    /**
     * Supported locales
     */
    protected array $supportedLocales = ['en', 'es', 'fr', 'de', 'it', 'pt', 'ar', 'zh', 'ja', 'ko'];

    /**
     * Cache duration for translations (in minutes)
     */
    protected int $cacheDuration = 60;

    public function __construct()
    {
        $this->defaultLocale = config('app.locale', 'en');
        $this->supportedLocales = config('app.supported_locales', $this->supportedLocales);
    }

    /**
     * Get all supported locales
     */
    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    /**
     * Check if locale is supported
     */
    public function isLocaleSupported(string $locale): bool
    {
        return in_array($locale, $this->supportedLocales);
    }

    /**
     * Get current locale
     */
    public function getCurrentLocale(): string
    {
        return App::getLocale() ?? $this->defaultLocale;
    }

    /**
     * Get default locale
     */
    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }

    /**
     * Bulk translate multiple models and fields
     */
    public function bulkTranslate(array $models, array $fields, string $locale, array $translations): int
    {
        $count = 0;

        foreach ($models as $model) {
            if (! method_exists($model, 'setTranslations')) {
                continue;
            }

            $modelTranslations = [];
            foreach ($fields as $field) {
                if (isset($translations[$model->getKey()][$field])) {
                    $modelTranslations[$field] = $translations[$model->getKey()][$field];
                }
            }

            if (! empty($modelTranslations)) {
                $model->setTranslations($modelTranslations, $locale);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Export translations for a model type
     */
    public function exportTranslations(string $modelClass, ?string $locale = null): array
    {
        $locale = $locale ?? $this->getCurrentLocale();

        $translations = Translation::where('translatable_type', $modelClass)
            ->where('locale', $locale)
            ->get()
            ->groupBy('translatable_id');

        $result = [];
        foreach ($translations as $modelId => $modelTranslations) {
            $result[$modelId] = $modelTranslations->pluck('value', 'field')->toArray();
        }

        return $result;
    }

    /**
     * Import translations for a model type
     */
    public function importTranslations(string $modelClass, array $translations, string $locale): int
    {
        $count = 0;

        foreach ($translations as $modelId => $fields) {
            foreach ($fields as $field => $value) {
                if (! empty($value)) {
                    Translation::setTranslation($modelClass, $modelId, $locale, $field, $value);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats(): array
    {
        $stats = [
            'total_translations' => Translation::count(),
            'by_locale' => [],
            'by_model' => [],
            'completion_rates' => [],
        ];

        // Count by locale
        $localeStats = Translation::selectRaw('locale, COUNT(*) as count')
            ->groupBy('locale')
            ->get()
            ->pluck('count', 'locale')
            ->toArray();

        $stats['by_locale'] = $localeStats;

        // Count by model type
        $modelStats = Translation::selectRaw('translatable_type, COUNT(*) as count')
            ->groupBy('translatable_type')
            ->get()
            ->pluck('count', 'translatable_type')
            ->toArray();

        $stats['by_model'] = $modelStats;

        // Calculate completion rates for each locale
        foreach ($this->supportedLocales as $locale) {
            $totalPossible = Translation::distinct('translatable_type', 'translatable_id', 'field')->count();
            $actualCount = Translation::where('locale', $locale)->count();

            $stats['completion_rates'][$locale] = $totalPossible > 0
              ? round(($actualCount / $totalPossible) * 100, 2)
              : 0;
        }

        return $stats;
    }

    /**
     * Find missing translations
     */
    public function findMissingTranslations(string $modelClass, ?string $locale = null): array
    {
        $locale = $locale ?? $this->getCurrentLocale();

        // Get all unique field/model combinations
        $allCombinations = Translation::where('translatable_type', $modelClass)
            ->distinct('translatable_id', 'field')
            ->get(['translatable_id', 'field']);

        // Get existing translations for the locale
        $existingTranslations = Translation::where('translatable_type', $modelClass)
            ->where('locale', $locale)
            ->get()
            ->map(function ($t) {
                return $t->translatable_id.'|'.$t->field;
            })
            ->toArray();

        // Find missing combinations
        $missing = [];
        foreach ($allCombinations as $combination) {
            $key = $combination->translatable_id.'|'.$combination->field;
            if (! in_array($key, $existingTranslations)) {
                $missing[] = [
                    'model_id' => $combination->translatable_id,
                    'field' => $combination->field,
                    'locale' => $locale,
                ];
            }
        }

        return $missing;
    }

    /**
     * Auto-translate missing translations using a callback
     */
    public function autoTranslate(string $modelClass, string $sourceLocale, string $targetLocale, callable $translator): int
    {
        $missing = $this->findMissingTranslations($modelClass, $targetLocale);
        $count = 0;

        foreach ($missing as $missingTranslation) {
            // Get source translation
            $sourceValue = Translation::getTranslation(
                $modelClass,
                $missingTranslation['model_id'],
                $sourceLocale,
                $missingTranslation['field']
            );

            if ($sourceValue) {
                // Translate using the callback
                $translatedValue = $translator($sourceValue, $sourceLocale, $targetLocale);

                if ($translatedValue && $translatedValue !== $sourceValue) {
                    Translation::setTranslation(
                        $modelClass,
                        $missingTranslation['model_id'],
                        $targetLocale,
                        $missingTranslation['field'],
                        $translatedValue
                    );
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Duplicate translations from one locale to another
     */
    public function duplicateLocale(string $sourceLocale, string $targetLocale, ?string $modelClass = null): int
    {
        $query = Translation::where('locale', $sourceLocale);

        if ($modelClass) {
            $query->where('translatable_type', $modelClass);
        }

        $sourceTranslations = $query->get();
        $count = 0;

        foreach ($sourceTranslations as $translation) {
            // Check if target translation already exists
            $exists = Translation::where([
                'translatable_type' => $translation->translatable_type,
                'translatable_id' => $translation->translatable_id,
                'locale' => $targetLocale,
                'field' => $translation->field,
            ])->exists();

            if (! $exists) {
                Translation::create([
                    'translatable_type' => $translation->translatable_type,
                    'translatable_id' => $translation->translatable_id,
                    'locale' => $targetLocale,
                    'field' => $translation->field,
                    'value' => $translation->value,
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Clean up empty or null translations
     */
    public function cleanupTranslations(): int
    {
        return Translation::whereNull('value')
            ->orWhere('value', '')
            ->delete();
    }

    /**
     * Get cached translations for better performance
     */
    public function getCachedTranslations(string $modelClass, int $modelId, string $locale): array
    {
        $cacheKey = "translations:{$modelClass}:{$modelId}:{$locale}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($modelClass, $modelId, $locale) {
            return Translation::getTranslations($modelClass, $modelId, $locale);
        });
    }

    /**
     * Clear translation cache
     */
    public function clearTranslationCache(string $modelClass, int $modelId, ?string $locale = null): void
    {
        if ($locale) {
            $cacheKey = "translations:{$modelClass}:{$modelId}:{$locale}";
            Cache::forget($cacheKey);
        } else {
            // Clear all locales for this model
            foreach ($this->supportedLocales as $loc) {
                $cacheKey = "translations:{$modelClass}:{$modelId}:{$loc}";
                Cache::forget($cacheKey);
            }
        }
    }

    /**
     * Validate translation data
     */
    public function validateTranslation(string $locale, string $field, string $value): array
    {
        $errors = [];

        if (! $this->isLocaleSupported($locale)) {
            $errors[] = "Locale '{$locale}' is not supported";
        }

        if (empty(trim($field))) {
            $errors[] = 'Field name cannot be empty';
        }

        if (strlen($value) > 65535) { // longText limit
            $errors[] = 'Translation value is too long (maximum 65535 characters)';
        }

        return $errors;
    }

    /**
     * Get locale display name
     */
    public function getLocaleDisplayName(string $locale): string
    {
        $names = [
            'en' => 'English',
            'km' => 'Khmer',
            'es' => 'Español',
            'fr' => 'Français',
            'de' => 'Deutsch',
            'it' => 'Italiano',
            'pt' => 'Português',
            'ar' => 'العربية',
            'zh' => '中文',
            'ja' => '日本語',
            'ko' => '한국어',
        ];

        return $names[$locale] ?? $locale;
    }

    /**
     * Get RTL (Right-to-Left) locales
     */
    public function getRTLLocales(): array
    {
        return ['ar', 'he', 'fa', 'ur'];
    }

    /**
     * Check if locale is RTL
     */
    public function isRTL(string $locale): bool
    {
        return in_array($locale, $this->getRTLLocales());
    }
}
