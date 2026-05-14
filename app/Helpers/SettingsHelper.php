<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('setting')) {
    /**
     * Get a setting value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting($key, $default = null)
    {
        $settings = Cache::remember('app_settings', 3600, function () {
            return Setting::pluck('value', 'key')->toArray();
        });

        return $settings[$key] ?? $default;
    }
}

if (!function_exists('settings_group')) {
    /**
     * Get all settings for a specific group
     *
     * @param string $group
     * @return array
     */
    function settings_group($group)
    {
        return Setting::where('group', $group)->pluck('value', 'key')->toArray();
    }
}

if (!function_exists('update_setting')) {
    /**
     * Update or create a setting
     *
     * @param string $key
     * @param mixed $value
     * @param string $type
     * @return void
     */
    function update_setting($key, $value, $type = 'string')
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => $type]
        );
        Cache::forget('app_settings');
    }
}

if (!function_exists('delete_setting')) {
    /**
     * Delete a setting
     *
     * @param string $key
     * @return void
     */
    function delete_setting($key)
    {
        Setting::where('key', $key)->delete();
        Cache::forget('app_settings');
    }
}

if (!function_exists('has_setting')) {
    /**
     * Check if a setting exists
     *
     * @param string $key
     * @return bool
     */
    function has_setting($key)
    {
        return Setting::where('key', $key)->exists();
    }
}
