<?php

namespace App\Traits;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

trait HasTranslations
{
    /**
     * The fields that can be translated
     * This should be defined in the model using this trait
     */

    /**
     * The default locale
     */
    protected string $defaultLocale = 'en';

    /**
     * Current locale for translations
     */
    protected ?string $currentLocale = null;

    /**
     * Cache prefix for translations
     */
    protected string $translationCachePrefix = 'translation:';

    /**
     * Boot the trait
     */
    public static function bootHasTranslations(): void
    {
        // Clear translation cache when model is updated or deleted
        static::saved(function ($model) {
            $model->clearTranslationCache();
        });

        static::deleted(function ($model) {
            $model->deleteAllTranslations();
            $model->clearTranslationCache();
        });
    }

    /**
     * Get all translations for this model
     */
    public function translations(): MorphMany
    {
        return $this->morphMany(Translation::class, 'translatable');
    }

    /**
     * Get the translatable fields for this model
     */
    public function getTranslatableFields(): array
    {
        return $this->translatable ?? [];
    }

    /**
     * Set the translatable fields for this model
     */
    public function setTranslatableFields(array $fields): self
    {
        $this->translatable = $fields;

        return $this;
    }

    /**
     * Check if a field is translatable
     */
    public function isTranslatable(string $field): bool
    {
        return in_array($field, $this->getTranslatableFields());
    }

    /**
     * Get current locale
     */
    public function getCurrentLocale(): string
    {
        return $this->currentLocale ?? App::getLocale() ?? $this->defaultLocale;
    }

    /**
     * Set current locale for this instance
     */
    public function setCurrentLocale(string $locale): self
    {
        $this->currentLocale = $locale;

        return $this;
    }

    /**
     * Get translation for a specific field and locale
     */
    public function getTranslation(string $field, ?string $locale = null): ?string
    {
        if (! $this->isTranslatable($field)) {
            return $this->getAttribute($field);
        }

        $locale = $locale ?? $this->getCurrentLocale();

        // Check cache first
        $cacheKey = $this->getTranslationCacheKey($field, $locale);
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $translation = Translation::getTranslation(
            $this->getMorphClass(),
            $this->getKey(),
            $locale,
            $field
        );

        // Cache the result
        Cache::put($cacheKey, $translation, now()->addHour());

        // Fallback to default locale if translation not found
        if ($translation === null && $locale !== $this->defaultLocale) {
            return $this->getTranslation($field, $this->defaultLocale);
        }

        // Fallback to original attribute if no translation found
        return $translation ?? $this->getAttribute($field);
    }

    /**
     * Set translation for a specific field and locale
     */
    public function setTranslation(string $field, string $value, ?string $locale = null): self
    {
        if (! $this->isTranslatable($field)) {
            $this->setAttribute($field, $value);

            return $this;
        }

        $locale = $locale ?? $this->getCurrentLocale();

        Translation::setTranslation(
            $this->getMorphClass(),
            $this->getKey(),
            $locale,
            $field,
            $value
        );

        // Clear cache for this translation
        $this->clearTranslationFieldCache($field, $locale);

        return $this;
    }

    /**
     * Get all translations for a specific locale
     */
    public function getTranslations(?string $locale = null): array
    {
        $locale = $locale ?? $this->getCurrentLocale();

        return Translation::getTranslations(
            $this->getMorphClass(),
            $this->getKey(),
            $locale
        );
    }

    /**
     * Set multiple translations for a specific locale
     */
    public function setTranslations(array $translations, ?string $locale = null): self
    {
        $locale = $locale ?? $this->getCurrentLocale();

        foreach ($translations as $field => $value) {
            if ($this->isTranslatable($field) && ! empty($value)) {
                $this->setTranslation($field, $value, $locale);
            }
        }

        return $this;
    }

    /**
     * Delete translation for a specific field and locale
     */
    public function deleteTranslation(string $field, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->getCurrentLocale();

        $deleted = $this->translations()
            ->where('locale', $locale)
            ->where('field', $field)
            ->delete() > 0;

        if ($deleted) {
            $this->clearTranslationFieldCache($field, $locale);
        }

        return $deleted;
    }

    /**
     * Delete all translations for a specific locale
     */
    public function deleteLocaleTranslations(?string $locale = null): bool
    {
        $locale = $locale ?? $this->getCurrentLocale();

        $deleted = $this->translations()->where('locale', $locale)->delete() > 0;

        if ($deleted) {
            $this->clearTranslationCache();
        }

        return $deleted;
    }

    /**
     * Delete all translations for this model
     */
    public function deleteAllTranslations(): bool
    {
        $deleted = $this->translations()->delete() > 0;

        if ($deleted) {
            $this->clearTranslationCache();
        }

        return $deleted;
    }

    /**
     * Check if translation exists for a field and locale
     */
    public function hasTranslation(string $field, ?string $locale = null): bool
    {
        $locale = $locale ?? $this->getCurrentLocale();

        return Translation::hasTranslation(
            $this->getMorphClass(),
            $this->getKey(),
            $locale,
            $field
        );
    }

    /**
     * Get all available locales for this model
     */
    public function getAvailableLocales(): array
    {
        return Translation::getAvailableLocales(
            $this->getMorphClass(),
            $this->getKey()
        );
    }

    /**
     * Check if model has translations in a specific locale
     */
    public function hasLocale(string $locale): bool
    {
        return in_array($locale, $this->getAvailableLocales());
    }

    /**
     * Get translation or fallback to original attribute
     */
    public function translate(string $field, ?string $locale = null): ?string
    {
        return $this->getTranslation($field, $locale);
    }

    /**
     * Get translated version of the model for a specific locale
     */
    public function translateTo(string $locale): self
    {
        $translated = clone $this;
        $translated->setCurrentLocale($locale);

        // Load translated values for all translatable fields
        foreach ($this->getTranslatableFields() as $field) {
            $translation = $this->getTranslation($field, $locale);
            if ($translation !== null) {
                $translated->setAttribute($field, $translation);
            }
        }

        return $translated;
    }

    /**
     * Get model with translations for all available locales
     */
    public function withAllTranslations(): array
    {
        $result = [];
        $locales = $this->getAvailableLocales();

        // Include the default locale if not in available locales
        if (! in_array($this->defaultLocale, $locales)) {
            $locales[] = $this->defaultLocale;
        }

        foreach ($locales as $locale) {
            $result[$locale] = $this->translateTo($locale)->toArray();
            $result[$locale]['_translations'] = $this->getTranslations($locale);
        }

        return $result;
    }

    /**
     * Copy translations from another model
     */
    public function copyTranslationsFrom($sourceModel, ?array $fields = null): self
    {
        if (! method_exists($sourceModel, 'getTranslations')) {
            return $this;
        }

        $fields = $fields ?? $this->getTranslatableFields();
        $locales = $sourceModel->getAvailableLocales();

        foreach ($locales as $locale) {
            $translations = $sourceModel->getTranslations($locale);

            foreach ($fields as $field) {
                if (isset($translations[$field]) && $this->isTranslatable($field)) {
                    $this->setTranslation($field, $translations[$field], $locale);
                }
            }
        }

        return $this;
    }

    /**
     * Get translation cache key
     */
    protected function getTranslationCacheKey(string $field, string $locale): string
    {
        return $this->translationCachePrefix.$this->getMorphClass().':'.$this->getKey().':'.$field.':'.$locale;
    }

    /**
     * Clear translation cache for a specific field and locale
     */
    protected function clearTranslationFieldCache(string $field, string $locale): void
    {
        $cacheKey = $this->getTranslationCacheKey($field, $locale);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all translation cache for this model
     */
    protected function clearTranslationCache(): void
    {
        $pattern = $this->translationCachePrefix.$this->getMorphClass().':'.$this->getKey().':*';
        // Note: This is a simplified approach. In production, you might want to use Redis with pattern matching
        foreach ($this->getTranslatableFields() as $field) {
            foreach ($this->getAvailableLocales() as $locale) {
                $this->clearTranslationFieldCache($field, $locale);
            }
        }
    }

    /**
     * Override getAttribute to return translations when available
     */
    public function getAttribute($key)
    {
        // If it's a translatable field and we have a current locale set, return translation
        if ($this->isTranslatable($key) && $this->currentLocale && $this->exists) {
            $translation = $this->getTranslation($key, $this->currentLocale);
            if ($translation !== null) {
                return $translation;
            }
        }

        return parent::getAttribute($key);
    }

    /**
     * Get translatable attributes array
     */
    public function getTranslatableAttributes(): array
    {
        $attributes = [];
        foreach ($this->getTranslatableFields() as $field) {
            $attributes[$field] = $this->getTranslation($field);
        }

        return $attributes;
    }

    /**
     * Get available translation status for all fields
     */
    public function getTranslationStatus(): array
    {
        $status = [];
        $locales = $this->getAvailableLocales();

        foreach ($this->getTranslatableFields() as $field) {
            $status[$field] = [];
            foreach ($locales as $locale) {
                $status[$field][$locale] = $this->hasTranslation($field, $locale);
            }
        }

        return $status;
    }

    /**
     * Get translation completeness percentage for a locale
     */
    public function getTranslationCompleteness(?string $locale = null): float
    {
        $locale = $locale ?? $this->getCurrentLocale();
        $translatableFields = $this->getTranslatableFields();

        if (empty($translatableFields)) {
            return 100.0;
        }

        $translatedCount = 0;
        foreach ($translatableFields as $field) {
            if ($this->hasTranslation($field, $locale)) {
                $translatedCount++;
            }
        }

        return (float) (($translatedCount / count($translatableFields)) * 100);
    }
}
