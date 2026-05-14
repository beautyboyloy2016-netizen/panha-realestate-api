<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\BaseController;
use App\Models\Translation;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Cache;

class TranslationController extends BaseController
{
    protected string $resource = 'translation';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $locales = Translation::getAvailableLocales();
        $models = Translation::getTranslatableModels();

        return view('admin.translations.index', compact('locales', 'models'));
    }

    /**
     * Get DataTable data
     */
    private function getDataTableData(Request $request)
    {
        $query = Translation::with('translatable')
            ->select('translations.*');

        // Apply filters
        if ($request->filled('locale')) {
            $query->where('locale', $request->locale);
        }

        if ($request->filled('translatable_type')) {
            $query->where('translatable_type', $request->translatable_type);
        }

        if ($request->filled('field')) {
            $query->where('field', 'like', '%' . $request->field . '%');
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('model_type', function ($translation) {
                $models = Translation::getTranslatableModels();
                $type = $translation->translatable_type;
                return $models[$type] ?? class_basename($type);
            })
            ->addColumn('model_id', function ($translation) {
                return '#' . $translation->translatable_id;
            })
            ->addColumn('locale_name', function ($translation) {
                return Translation::getLocaleDisplayName($translation->locale);
            })
            ->addColumn('value_preview', function ($translation) {
                $value = $translation->value ?? '';
                $preview = str($value)->limit(50);
                return '<span class="text-truncate d-inline-block" style="max-width: 200px;" title="' . htmlspecialchars($value) . '">' .
                       htmlspecialchars($preview) .
                       '</span>';
            })
            ->addColumn('actions', function ($translation) {
                $editBtn = '<button type="button" class="btn btn-sm btn-primary edit-translation"
                                data-id="' . $translation->id . '"
                                data-locale="' . $translation->locale . '"
                                data-field="' . $translation->field . '"
                                data-value="' . htmlspecialchars($translation->value) . '"
                                title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>';

                $deleteBtn = '<button type="button" class="btn btn-sm btn-danger delete-translation"
                                  data-id="' . $translation->id . '"
                                  title="Delete">
                                  <i class="fas fa-trash"></i>
                              </button>';

                return '<div class="btn-group" role="group">' . $editBtn . ' ' . $deleteBtn . '</div>';
            })
            ->rawColumns(['value_preview', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'translatable_type' => 'required|string',
            'translatable_id' => 'required|integer',
            'locale' => 'required|string|size:2',
            'field' => 'required|string',
            'value' => 'nullable|string',
        ]);

        try {
            $translation = Translation::create($validated);

            // Clear cache
            $this->clearTranslationCache($translation);

            return response()->json([
                'success' => true,
                'message' => 'Translation created successfully!',
                'data' => $translation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Translation $translation)
    {
        $validated = $request->validate([
            'locale' => 'sometimes|required|string|size:2',
            'field' => 'sometimes|required|string',
            'value' => 'nullable|string',
        ]);

        try {
            $translation->update($validated);

            // Clear cache
            $this->clearTranslationCache($translation);

            return response()->json([
                'success' => true,
                'message' => 'Translation updated successfully!',
                'data' => $translation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update inline field (for inline editing)
     */
    public function updateInline(Request $request, Translation $translation)
    {
        $validated = $request->validate([
            'field' => 'required|string|in:value',
            'value' => 'nullable|string',
        ]);

        try {
            $translation->update([
                $validated['field'] => $validated['value']
            ]);

            // Clear cache
            $this->clearTranslationCache($translation);

            return response()->json([
                'success' => true,
                'message' => 'Translation updated successfully!',
                'data' => $translation
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Translation $translation)
    {
        try {
            // Clear cache before deletion
            $this->clearTranslationCache($translation);

            $translation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Translation deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete translation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk delete translations
     */
    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:translations,id'
        ]);

        try {
            Translation::whereIn('id', $validated['ids'])->delete();

            // Clear cache
            Cache::tags(['translations'])->flush();

            return response()->json([
                'success' => true,
                'message' => count($validated['ids']) . ' translations deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete translations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear translation cache
     */
    private function clearTranslationCache(Translation $translation)
    {
        $cacheKey = "translation:{$translation->translatable_type}:{$translation->translatable_id}";
        Cache::forget($cacheKey);
        Cache::tags(['translations'])->flush();
    }
}
