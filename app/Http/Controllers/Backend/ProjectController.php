<?php

namespace App\Http\Controllers\Backend;

use App\Models\Project;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Yajra\DataTables\Facades\DataTables;

class ProjectController extends BaseController
{
    use HasPermissions;

    protected string $resource = 'project';

    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        // Check permissions
        $this->authorizeResource('projects', 'view');

        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        return view('admin.projects.index');
    }

    /**
     * Get data for DataTables Ajax using Yajra DataTables
     */
    private function getDataTableData(Request $request)
    {
        $locale = $request->get('locale', App::getLocale());

        $query = Project::with(['entityMedia.media']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('image', function ($project) {
                // Only use HasMedia trait (entityMedia) - no legacy fallback
                $imageUrl = asset('images/no-image.png');
                if ($project->relationLoaded('entityMedia') && $project->entityMedia->isNotEmpty()) {
                    $featuredMedia = $project->entityMedia->firstWhere('zone', 'primary_image');
                    if ($featuredMedia && $featuredMedia->media) {
                        $imageUrl = $featuredMedia->media->full_url;
                    }
                }

                return '<img src="'.$imageUrl.'" alt="Project" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
            })
            ->addColumn('name', function ($project) use ($locale) {
                // Using HasTranslations trait to get translated name [[12]]
                $name = $project->getTranslation('name', $locale);
                $developer = $project->getTranslation('developer', $locale);
                return '<strong>'.$name.'</strong><br>
                        <small class="text-muted">'.$developer.'</small>';
            })
            ->addColumn('location', function ($project) use ($locale) {
                // Using HasTranslations for location [[12]]
                return $project->getTranslation('location', $locale);
            })
            ->addColumn('units', function ($project) {
                return '<span class="badge bg-info">'.$project->units.' units</span>';
            })
            ->addColumn('price', function ($project) use ($locale) {
                // Using HasTranslations for price_from [[12]]
                $price = $project->getTranslation('price_from', $locale);
                return '<strong>'.$price.'</strong>';
            })
            ->addColumn('completion', function ($project) use ($locale) {
                // Using HasTranslations for completion [[12]]
                $completion = $project->getTranslation('completion', $locale);
                return '<span class="badge bg-secondary">'.$completion.'</span>';
            })
            ->addColumn('yield', function ($project) {
                if ($project->rental_yield) {
                    return '<span class="badge bg-success">'.$project->rental_yield.'%</span>';
                }
                return '<span class="text-muted">N/A</span>';
            })
            ->addColumn('status', function ($project) {
                $badges = [];
                if ($project->featured) {
                    $badges[] = '<span class="badge bg-warning text-dark">Featured</span>';
                }

                // Show translation completeness [[12]]
                $completeness = $project->getTranslationCompleteness();
                if ($completeness < 100) {
                    $badges[] = '<span class="badge bg-info" title="Translation Completeness">'.round($completeness).'%</span>';
                }

                return implode(' ', $badges) ?: '<span class="text-muted">Standard</span>';
            })
            ->addColumn('actions', function ($project) {
                $actions = [];
                $showUrl = route('admin.projects.show', $project->id);

                // Check permissions for each action [[14]]
                $actions[] = '<a href="'.$showUrl.'" class="btn btn-sm btn-info" title="View">
                    <i class="fas fa-eye"></i>
                </a>';

                if ($this->canCRUD('project', 'edit')) {
                    $editUrl = route('admin.projects.edit', $project->id);
                    $actions[] = '<a href="'.$editUrl.'" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>';
                    $actions[] = '<button class="btn btn-sm btn-primary translate-project" data-id="'.$project->id.'" title="Translations">
                        <i class="fas fa-language"></i>
                    </button>';
                }

                if ($this->canCRUD('project', 'delete')) {
                    $actions[] = '<button class="btn btn-sm btn-danger delete-project" data-id="'.$project->id.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }

                return '<div class="btn-group">' . implode('', $actions) . '</div>';
            })
            ->editColumn('created_at', function ($project) {
                return $project->created_at->format('Y-m-d H:i');
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->filterColumn('location', function ($query, $keyword) {
                $query->where('location', 'like', "%{$keyword}%");
            })
            ->rawColumns(['image', 'name', 'location', 'units', 'price', 'completion', 'yield', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeResource('projects', 'create');

        $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);

        return view('admin.projects.create', compact('availableLocales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeResource('projects', 'create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'developer' => 'required|string|max:255',
            'units' => 'required|integer|min:1',
            'price_from' => 'required|string',
            'completion' => 'required|string',
            'featured' => 'boolean',
            'rental_yield' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',

            // Translations [[12]]
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required_with:translations|string|max:5',
            'translations.*.name' => 'nullable|string|max:255',
            'translations.*.location' => 'nullable|string|max:255',
            'translations.*.developer' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.price_from' => 'nullable|string',
            'translations.*.completion' => 'nullable|string',

            // Media [[13]]
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery_image_ids' => 'nullable|array',
            'gallery_image_ids.*' => 'exists:media,id',
            'document_ids' => 'nullable|array',
            'document_ids.*' => 'exists:media,id',

            // Meta data [[11]]
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $project = Project::create($validated);

        // Handle translations [[12]]
        if ($request->has('translations')) {
            foreach ($request->translations as $translation) {
                if (!empty($translation['locale'])) {
                    $project->setTranslations([
                        'name' => $translation['name'] ?? null,
                        'location' => $translation['location'] ?? null,
                        'developer' => $translation['developer'] ?? null,
                        'description' => $translation['description'] ?? null,
                        'price_from' => $translation['price_from'] ?? null,
                        'completion' => $translation['completion'] ?? null,
                    ], $translation['locale']);
                }
            }
        }

        // Handle primary image using HasMedia trait [[13]]
        if ($request->has('featured_image_id')) {
            $project->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Handle gallery images [[13]]
        if ($request->has('gallery_image_ids')) {
            $project->syncMediaForZone($request->gallery_image_ids, 'gallery');
        }

        // Handle project documents [[13]]
        if ($request->has('document_ids')) {
            $project->syncMediaForZone($request->document_ids, 'documents');
        }

        // Meta data is handled automatically by HasMetaData trait boot method [[11]]

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Project created successfully!',
                'project' => $project->load('entityMedia.media', 'translations'),
            ]);
        }

        sweetalert()->success('Project created successfully!');
        return redirect()->route('admin.projects.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $this->authorizeResource('projects', 'view');

        $project->load(['entityMedia.media', 'translations', 'meta']);

        // Get all available translations
        $availableLocales = $project->getAvailableLocales();
        $translationStatus = $project->getTranslationStatus();

        return view('admin.projects.show', compact('project', 'availableLocales', 'translationStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Project $project)
    {
        $this->authorizeResource('projects', 'update');

        $project->load(['entityMedia.media', 'translations', 'meta']);

        if ($request->ajax()) {
            // Include translations for all locales
            $projectData = $project->toArray();
            $projectData['all_translations'] = $project->withAllTranslations();

            // Get primary image from loaded relationship
            $featuredEntityMedia = $project->entityMedia->firstWhere('zone', 'primary_image');
            $projectData['featured_image'] = $featuredEntityMedia && $featuredEntityMedia->media ? [
                'id' => $featuredEntityMedia->media->id,
                'full_url' => $featuredEntityMedia->media->full_url,
                'file_url' => $featuredEntityMedia->media->file_url ?? $featuredEntityMedia->media->full_url,
                'original_name' => $featuredEntityMedia->media->original_name,
                'file_name' => $featuredEntityMedia->media->file_name,
                'file_size' => $featuredEntityMedia->media->file_size,
                'mime_type' => $featuredEntityMedia->media->mime_type,
            ] : null;

            // Get gallery images from loaded relationship
            $galleryEntityMedia = $project->entityMedia->where('zone', 'gallery');
            $projectData['gallery_images'] = $galleryEntityMedia->map(function ($em) {
                return [
                    'id' => $em->media->id,
                    'full_url' => $em->media->full_url,
                    'file_url' => $em->media->file_url ?? $em->media->full_url,
                    'original_name' => $em->media->original_name,
                    'file_name' => $em->media->file_name,
                    'file_size' => $em->media->file_size,
                    'mime_type' => $em->media->mime_type,
                ];
            })->values();

            // Get documents from loaded relationship
            $documentsEntityMedia = $project->entityMedia->where('zone', 'documents');
            $projectData['documents'] = $documentsEntityMedia->map(function ($em) {
                return [
                    'id' => $em->media->id,
                    'full_url' => $em->media->full_url,
                    'file_url' => $em->media->file_url ?? $em->media->full_url,
                    'original_name' => $em->media->original_name,
                    'file_name' => $em->media->file_name,
                    'file_size' => $em->media->file_size,
                    'mime_type' => $em->media->mime_type,
                ];
            })->values();

            return response()->json($projectData);
        }

        $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);

        return view('admin.projects.edit', compact('project', 'availableLocales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $this->authorizeResource('projects', 'update');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'developer' => 'required|string|max:255',
            'units' => 'required|integer|min:1',
            'price_from' => 'required|string',
            'completion' => 'required|string',
            'featured' => 'boolean',
            'rental_yield' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',

            // Translations
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required_with:translations|string|max:5',
            'translations.*.name' => 'nullable|string|max:255',
            'translations.*.location' => 'nullable|string|max:255',
            'translations.*.developer' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.price_from' => 'nullable|string',
            'translations.*.completion' => 'nullable|string',

            // Media
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery_image_ids' => 'nullable|array',
            'gallery_image_ids.*' => 'exists:media,id',
            'document_ids' => 'nullable|array',
            'document_ids.*' => 'exists:media,id',

            // Meta data
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $project->update($validated);

        // Handle translations [[12]]
        if ($request->has('translations')) {
            foreach ($request->translations as $translation) {
                if (!empty($translation['locale'])) {
                    $project->setTranslations([
                        'name' => $translation['name'] ?? null,
                        'location' => $translation['location'] ?? null,
                        'developer' => $translation['developer'] ?? null,
                        'description' => $translation['description'] ?? null,
                        'price_from' => $translation['price_from'] ?? null,
                        'completion' => $translation['completion'] ?? null,
                    ], $translation['locale']);
                }
            }
        }

        // Update primary image [[13]]
        if ($request->has('featured_image_id')) {
            $project->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Update gallery images [[13]]
        if ($request->has('gallery_image_ids')) {
            $project->syncMediaForZone($request->gallery_image_ids, 'gallery');
        }

        // Update documents [[13]]
        if ($request->has('document_ids')) {
            $project->syncMediaForZone($request->document_ids, 'documents');
        }

        // Meta data is handled automatically by HasMetaData trait boot method [[11]]

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Project updated successfully!',
                'project' => $project->load('entityMedia.media', 'translations'),
            ]);
        }

        sweetalert()->success('Project updated successfully!');
        return redirect()->route('admin.projects.index');
    }

    /**
     * Handle translations for a project
     */
    public function translations(Request $request, Project $project)
    {
        $this->authorizeResource('projects', 'update');

        if ($request->isMethod('get')) {
            $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);
            $translations = [];

            foreach ($availableLocales as $locale) {
                $translations[$locale] = $project->getTranslations($locale);
                $translations[$locale]['completeness'] = $project->getTranslationCompleteness($locale);
            }

            return response()->json([
                'translations' => $translations,
                'translatable_fields' => $project->getTranslatableFields(),
                'available_locales' => $availableLocales,
            ]);
        }

        // Update translations
        $validated = $request->validate([
            'locale' => 'required|string|max:5',
            'translations' => 'required|array',
        ]);

        $project->setTranslations($validated['translations'], $validated['locale']);

        return response()->json([
            'success' => true,
            'message' => '✅ Translations updated successfully!',
            'completeness' => $project->getTranslationCompleteness($validated['locale']),
        ]);
    }

    /**
     * Duplicate a project with all its relationships
     */
    public function duplicate(Project $project)
    {
        $this->authorizeResource('projects', 'create');

        $newProject = $project->replicate();
        $newProject->name = $project->name . ' (Copy)';
        $newProject->save();

        // Copy translations [[12]]
        $newProject->copyTranslationsFrom($project);

        // Copy media [[13]]
        foreach ($project->entityMedia as $media) {
            $newProject->addMediaToZone($media->media_id, $media->zone);
        }

        // Copy metadata [[11]]
        if ($project->meta && $project->meta->exists) {
            $newMeta = $newProject->meta()->create([]);

            // Copy metadata translations
            foreach ($project->meta->translations as $translation) {
                \App\Models\Translation::setTranslation(
                    \App\Models\MetaData::class,
                    $newMeta->id,
                    $translation->locale,
                    $translation->field,
                    $translation->value
                );
            }
        }

        sweetalert()->success('Project duplicated successfully!');
        return redirect()->route('admin.projects.edit', $newProject);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Project $project)
    {
        $this->authorizeResource('projects', 'update');

        $project->update(['featured' => !$project->featured]);

        return response()->json([
            'success' => true,
            'featured' => $project->featured,
            'message' => $project->featured ? 'Project featured!' : 'Project unfeatured!',
        ]);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $this->authorizeResource('projects', 'update');

        $validated = $request->validate([
            'action' => 'required|in:delete,feature,unfeature',
            'ids' => 'required|array',
            'ids.*' => 'exists:projects,id',
        ]);

        $projects = Project::whereIn('id', $validated['ids'])->get();

        switch ($validated['action']) {
            case 'delete':
                $this->authorizeResource('projects', 'delete');
                foreach ($projects as $project) {
                    $project->delete();
                }
                $message = 'Projects deleted successfully!';
                break;

            case 'feature':
                Project::whereIn('id', $validated['ids'])->update(['featured' => true]);
                $message = 'Projects featured successfully!';
                break;

            case 'unfeature':
                Project::whereIn('id', $validated['ids'])->update(['featured' => false]);
                $message = 'Projects unfeatured successfully!';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Export projects data
     */
    public function export(Request $request)
    {
        $this->authorizeResource('projects', 'view');

        $locale = $request->get('locale', App::getLocale());
        $projects = Project::all()->map(function ($project) use ($locale) {
            return $project->translateTo($locale);
        });

        // Implementation depends on your export package
        // Example: return Excel::download(new ProjectsExport($projects), 'projects.xlsx');

        return response()->json([
            'success' => true,
            'message' => 'Export functionality to be implemented',
            'data' => $projects,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project, Request $request)
    {
        $this->authorizeResource('projects', 'delete');

        // Soft delete will trigger trait boot methods to clean up related data
        $project->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ Project deleted successfully!',
            ]);
        }

        sweetalert()->success('Project deleted successfully!');
        return redirect()->route('admin.projects.index');
    }
}
