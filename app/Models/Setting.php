<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    /**
     * Get setting value with automatic type casting
     */
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

    /**
     * Set setting value with type handling
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            'json' => json_encode($value),
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    /**
     * Clear cache when settings change
     */
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

    /**
     * Get all settings as key-value array
     */
    public static function getAllSettings()
    {
        return Cache::remember('app_settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Get settings by group
     */
    public static function getByGroup($group)
    {
        return self::where('group', $group)->pluck('value', 'key')->toArray();
    }

    /**
     * Get public settings only
     */
    public static function getPublicSettings()
    {
        return self::where('is_public', true)
            ->get()
            ->groupBy('group')
            ->map(function ($group) {
                return $group->pluck('value', 'key');
            });
    }

    /**
     * Get a specific setting value
     */
    public static function get($key, $default = null)
    {
        $settings = self::getAllSettings();

        if (!isset($settings[$key])) {
            return $default;
        }

        $setting = self::where('key', $key)->first();

        if (!$setting) {
            return $default;
        }

        return $setting->value;
    }

    /**
     * Set a specific setting value
     */
    public static function set($key, $value)
    {
        $setting = self::where('key', $key)->first();

        if ($setting) {
            $setting->value = $value;
            $setting->save();
        } else {
            self::create([
                'key' => $key,
                'value' => $value,
                'type' => is_bool($value) ? 'boolean' : 'string',
                'group' => 'general',
            ]);
        }

        Cache::forget('app_settings');

        return true;
    }

    /**
     * Check if social authentication is enabled
     */
    public static function isSocialAuthEnabled()
    {
        return (bool) self::get('auth.social.enabled', true);
    }

    /**
     * Check if Google login is enabled
     */
    public static function isGoogleLoginEnabled()
    {
        return self::isSocialAuthEnabled() && (bool) self::get('auth.google.enabled', true);
    }

    /**
     * Check if Telegram login is enabled
     */
    public static function isTelegramLoginEnabled()
    {
        return self::isSocialAuthEnabled() && (bool) self::get('auth.telegram.enabled', true);
    }

    /**
     * Check if a specific language is enabled
     */
    public static function isLanguageEnabled($locale)
    {
        return (bool) self::get("language.{$locale}.enabled", true);
    }

    /**
     * Get all enabled languages
     */
    public static function getEnabledLanguages()
    {
        $allLanguages = ['en', 'km', 'zh', 'fr'];
        $enabled = [];

        foreach ($allLanguages as $locale) {
            if (self::isLanguageEnabled($locale)) {
                $enabled[] = $locale;
            }
        }

        return $enabled;
    }

    /**
     * Get language display name
     */
    public static function getLanguageName($locale)
    {
        return self::get("language.{$locale}.name", ucfirst($locale));
    }

    /**
     * Get default language
     */
    public static function getDefaultLanguage()
    {
        return self::get('language.default', 'en');
    }
}
