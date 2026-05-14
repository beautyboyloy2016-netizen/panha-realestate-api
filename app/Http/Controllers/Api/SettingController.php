<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;

class SettingController extends Controller
{
    /**
     * Get all public settings
     */
    public function index()
    {
        $settings = Setting::getPublicSettings();

        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    /**
     * Get settings by group
     */
    public function group($group)
    {
        $settings = Setting::where('group', $group)
            ->where('is_public', true)
            ->pluck('value', 'key');

        return response()->json([
            'success' => true,
            'group' => $group,
            'settings' => $settings
        ]);
    }

    /**
     * Get a specific setting
     */
    public function show($key)
    {
        $setting = Setting::where('key', $key)
            ->where('is_public', true)
            ->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found or not public'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'key' => $setting->key,
            'value' => $setting->value
        ]);
    }
}
