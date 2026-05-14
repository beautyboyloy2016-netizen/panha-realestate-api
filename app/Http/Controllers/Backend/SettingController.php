<?php

namespace App\Http\Controllers\Backend;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SettingController extends BaseController
{
    protected string $resource = 'setting';

    /**
     * Display settings page
     */
    public function index()
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                // Handle boolean values - the value from form is already '0' or '1'
                if ($setting->type === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
                }

                $setting->update(['value' => $value]);
            }
        }

        Cache::forget('app_settings');

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    /**
     * Clear settings cache
     */
    public function clearCache()
    {
        Cache::forget('app_settings');

        return redirect()->back()->with('success', 'Settings cache cleared successfully');
    }

    /**
     * Quick toggle for boolean settings (AJAX)
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|exists:settings,key',
            'value' => 'required|boolean'
        ]);

        try {
            $setting = Setting::where('key', $validated['key'])->firstOrFail();

            if ($setting->type !== 'boolean') {
                return response()->json([
                    'success' => false,
                    'message' => 'This setting is not a boolean type'
                ], 400);
            }

            $setting->value = $validated['value'];
            $setting->save();

            Cache::forget('app_settings');

            return response()->json([
                'success' => true,
                'message' => 'Setting toggled successfully!',
                'value' => $setting->value
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle setting: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update single setting (AJAX)
     */
    public function updateSingle(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|exists:settings,key',
            'value' => 'required'
        ]);

        try {
            $setting = Setting::where('key', $validated['key'])->firstOrFail();
            $setting->value = $validated['value'];
            $setting->save();

            Cache::forget('app_settings');

            return response()->json([
                'success' => true,
                'message' => 'Setting updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage()
            ], 500);
        }
    }
}
