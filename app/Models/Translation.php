<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'translatable_type',
        'translatable_id',
        'locale',
        'field',
        'value',
    ];

    /**
     * Get the parent translatable model.
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all available locales
     */
    public static function getAvailableLocales(): array
    {
        return config('app.available_locales', ['en', 'km', 'zh', 'fr']);
    }

    /**
     * Get locale display name
     */
    public static function getLocaleDisplayName(string $locale): string
    {
        $names = [
            'en' => 'English',
            'km' => 'ខ្មែរ (Khmer)',
            'zh' => '中文 (Chinese)',
            'fr' => 'Français (French)',
        ];

        return $names[$locale] ?? $locale;
    }

    /**
     * Get all translatable models
     */
    public static function getTranslatableModels(): array
    {
        return [
            'App\Models\Property' => 'Property',
            'App\Models\Project' => 'Project',
            'App\Models\NewsArticle' => 'News Article',
        ];
    }

    /**
     * Scope to filter by locale
     */
    public function scopeLocale($query, string $locale)
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope to filter by translatable type
     */
    public function scopeTranslatableType($query, string $type)
    {
        return $query->where('translatable_type', $type);
    }

    /**
     * Scope to filter by field
     */
    public function scopeField($query, string $field)
    {
        return $query->where('field', $field);
    }

    /**
     * Get a translation value
     */
    public static function getTranslation(string $translatableType, int $translatableId, string $locale, string $field): ?string
    {
        $translation = static::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId)
            ->where('locale', $locale)
            ->where('field', $field)
            ->first();

        return $translation?->value;
    }

    /**
     * Check if a translation exists
     */
    public static function hasTranslation(string $translatableType, int $translatableId, string $locale, string $field): bool
    {
        return static::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId)
            ->where('locale', $locale)
            ->where('field', $field)
            ->exists();
    }

    /**
     * Set a translation value
     */
    public static function setTranslation(string $translatableType, int $translatableId, string $locale, string $field, string $value): void
    {
        static::updateOrCreate(
            [
                'translatable_type' => $translatableType,
                'translatable_id' => $translatableId,
                'locale' => $locale,
                'field' => $field,
            ],
            [
                'value' => $value,
            ]
        );
    }

    /**
     * Get all translations for a model in a specific locale
     */
    public static function getTranslations(string $translatableType, int $translatableId, string $locale): array
    {
        $translations = static::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId)
            ->where('locale', $locale)
            ->get();

        return $translations->pluck('value', 'field')->toArray();
    }

    /**
     * Delete translations for a model
     */
    public static function deleteTranslations(string $translatableType, int $translatableId, ?string $locale = null, ?string $field = null): int
    {
        $query = static::where('translatable_type', $translatableType)
            ->where('translatable_id', $translatableId);

        if ($locale) {
            $query->where('locale', $locale);
        }

        if ($field) {
            $query->where('field', $field);
        }

        return $query->delete();
    }
}
