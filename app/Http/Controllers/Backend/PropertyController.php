<?php

namespace App\Http\Controllers\Backend;

use App\Models\Feature;
use App\Models\Property;
use App\Models\PropertyType;
use App\Models\User;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Yajra\DataTables\Facades\DataTables;

class PropertyController extends BaseController
{
    use HasPermissions;

    protected string $resource = 'property';

    /**
     * Display a listing of properties.
     */
    public function index(Request $request)
    {
        // Check permissions
        $this->authorizeResource('properties', 'view');

        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $propertyTypes = PropertyType::active()->ordered()->get();
        $features = Feature::active()->ordered()->get();

        return view('admin.properties.index', compact('propertyTypes', 'features'));
    }

    /**
     * Get data for DataTables Ajax using Yajra DataTables
     */
    private function getDataTableData(Request $request)
    {
        $locale = $request->get('locale', App::getLocale());

        $query = Property::with(['user', 'entityMedia.media', 'translations']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('image', function ($property) {
                // Safety check to ensure we have a Property model
                if (!$property instanceof Property) {
                    return '<img src="'.asset('images/no-image.png').'" alt="Property" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
                }

                // Only use HasMedia trait (entityMedia) - no Unsplash fallback
                $imageUrl = asset('images/no-image.png');
                if ($property->relationLoaded('entityMedia') && $property->entityMedia->isNotEmpty()) {
                    $featuredMedia = $property->entityMedia->firstWhere('zone', 'primary_image');
                    if ($featuredMedia && $featuredMedia->media) {
                        $imageUrl = $featuredMedia->media->full_url;
                    }
                }

                return '<img src="'.$imageUrl.'" alt="Property" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
            })
            ->addColumn('title', function ($property) use ($locale) {
                // Safety check to ensure we have a Property model
                if (!$property instanceof Property) {
                    return '<span class="text-muted">Invalid data</span>';
                }
                // Using HasTranslations trait to get translated title
                $title = $property->getTranslation('title', $locale);
                return '<strong>'.$title.'</strong><br>
                        <small class="text-muted">'.$property->property_type.'</small>';
            })
            ->addColumn('listing', function ($property) {
                $badgeClass = $property->listing_type === 'For Sale' ? 'bg-primary' : 'bg-info';
                return '<span class="badge '.$badgeClass.'">'.$property->listing_type.'</span>';
            })
            ->addColumn('price', function ($property) {
                return '<strong>$'.number_format($property->price).'</strong><br>
                        <small class="text-muted">'.$property->area.' '.$property->area_unit.'</small>';
            })
            ->addColumn('location', function ($property) use ($locale) {
                // Safety check to ensure we have a Property model
                if (!$property instanceof Property) {
                    return '<span class="text-muted">N/A</span>';
                }
                // Using HasTranslations for location fields
                $location = $property->getTranslation('location', $locale);
                $district = $property->getTranslation('district', $locale);
                return $property->city.'<br><small class="text-muted">'.($district ?? 'N/A').'</small>';
            })
            ->addColumn('details', function ($property) {
                return '<i class="fas fa-bed"></i> '.$property->bedrooms.' &nbsp;
                        <i class="fas fa-bath"></i> '.$property->bathrooms;
            })
            ->addColumn('owner', function ($property) {
                return $property->user ? $property->user->full_name : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('status', function ($property) {
                // Safety check to ensure we have a Property model
                if (!$property instanceof Property) {
                    return '<span class="badge bg-secondary">N/A</span>';
                }
                $badges = [];
                if ($property->is_featured) {
                    $badges[] = '<span class="badge bg-warning text-dark">Featured</span>';
                }
                $badges[] = $property->is_available
                    ? '<span class="badge bg-success">Available</span>'
                    : '<span class="badge bg-danger">Unavailable</span>';

                // Show translation completeness
                $completeness = $property->getTranslationCompleteness();
                if ($completeness < 100) {
                    $badges[] = '<span class="badge bg-info" title="Translation Completeness">'.round($completeness).'%</span>';
                }

                return implode(' ', $badges);
            })
            ->addColumn('views', function ($property) {
                return '<span class="badge bg-secondary">'.$property->views.' views</span>';
            })
            ->addColumn('actions', function ($property) {
                $actions = [];
                $showUrl = route('admin.properties.show', $property->id);

                // Check permissions for each action
                $actions[] = '<a href="'.$showUrl.'" class="btn btn-sm btn-info" title="View">
                    <i class="fas fa-eye"></i>
                </a>';

                if ($this->canCRUD('property', 'edit')) {
                    $actions[] = '<button class="btn btn-sm btn-warning edit-property" data-id="'.$property->id.'" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>';
                    $actions[] = '<button class="btn btn-sm btn-primary translate-property" data-id="'.$property->id.'" title="Translations">
                        <i class="fas fa-language"></i>
                    </button>';
                }

                if ($this->canCRUD('property', 'delete')) {
                    $actions[] = '<button class="btn btn-sm btn-danger delete-property" data-id="'.$property->id.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }

                return '<div class="btn-group">' . implode('', $actions) . '</div>';
            })
            ->editColumn('created_at', function ($property) {
                return $property->created_at->format('Y-m-d H:i');
            })
            ->filterColumn('title', function ($query, $keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            })
            ->filterColumn('listing', function ($query, $keyword) {
                $query->where('listing_type', 'like', "%{$keyword}%");
            })
            ->filterColumn('location', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('city', 'like', "%{$keyword}%")
                      ->orWhere('district', 'like', "%{$keyword}%")
                      ->orWhere('location', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('owner', function ($query, $keyword) {
                $query->whereHas('user', function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                      ->orWhere('last_name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['image', 'title', 'listing', 'price', 'location', 'details', 'owner', 'status', 'views', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeResource('properties', 'create');

        $users = User::all();
        $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);

        return view('admin.properties.create', compact('users', 'availableLocales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeResource('properties', 'create');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'listing_type' => 'required|in:For Sale,For Rent',
            'property_type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required|numeric|min:0',
            'area_unit' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'features' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',

            // Translations [[7]]
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required_with:translations|string|max:5',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.location' => 'nullable|string|max:255',
            'translations.*.district' => 'nullable|string|max:100',
            'translations.*.features' => 'nullable|array',

            // Media [[6]]
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery_image_ids' => 'nullable|array',
            'gallery_image_ids.*' => 'exists:media,id',

            // Meta data [[9]]
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $property = Property::create($validated);

        // Handle translations [[7]]
        if ($request->has('translations')) {
            foreach ($request->translations as $translation) {
                if (!empty($translation['locale'])) {
                    $property->setTranslations([
                        'title' => $translation['title'] ?? null,
                        'description' => $translation['description'] ?? null,
                        'location' => $translation['location'] ?? null,
                        'district' => $translation['district'] ?? null,
                        'features' => $translation['features'] ?? null,
                    ], $translation['locale']);
                }
            }
        }

        // Handle primary image using HasMedia trait [[6]]
        if ($request->has('featured_image_id')) {
            $property->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Handle gallery images [[6]]
        if ($request->has('gallery_image_ids')) {
            $property->syncMediaForZone($request->gallery_image_ids, 'gallery');
        }

        // Meta data is handled automatically by HasMetaData trait boot method [[9]]

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Property created successfully!',
                'property' => $property->load('entityMedia.media', 'translations'),
            ]);
        }

        sweetalert()->success('Property created successfully!');
        return redirect()->route('admin.properties.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Property $property)
    {
        $this->authorizeResource('properties', 'view');

        $property->load(['user', 'entityMedia.media', 'translations', 'meta']);

        // Get all available translations
        $availableLocales = $property->getAvailableLocales();
        $translationStatus = $property->getTranslationStatus();

        return view('admin.properties.show', compact('property', 'availableLocales', 'translationStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Property $property)
    {
        $this->authorizeResource('properties', 'update');

        $property->load(['entityMedia.media', 'translations', 'meta']);

        if ($request->ajax()) {
            // Include translations for all locales
            $propertyData = $property->toArray();
            $propertyData['all_translations'] = $property->withAllTranslations();

            // Get primary image from loaded relationship
            $featuredEntityMedia = $property->entityMedia->firstWhere('zone', 'primary_image');
            $propertyData['featured_image'] = $featuredEntityMedia && $featuredEntityMedia->media ? [
                'id' => $featuredEntityMedia->media->id,
                'full_url' => $featuredEntityMedia->media->full_url,
                'file_url' => $featuredEntityMedia->media->file_url ?? $featuredEntityMedia->media->full_url,
                'original_name' => $featuredEntityMedia->media->original_name,
                'file_name' => $featuredEntityMedia->media->file_name,
                'file_size' => $featuredEntityMedia->media->file_size,
                'mime_type' => $featuredEntityMedia->media->mime_type,
            ] : null;

            // Get gallery images from loaded relationship
            $galleryEntityMedia = $property->entityMedia->where('zone', 'gallery');
            $propertyData['gallery_images'] = $galleryEntityMedia->map(function ($em) {
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

            return response()->json($propertyData);
        }

        $users = User::all();
        $propertyTypes = PropertyType::active()->ordered()->get();
        $features = Feature::active()->ordered()->get();
        $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);

        return view('admin.properties.edit', compact('property', 'users', 'propertyTypes', 'features', 'availableLocales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        $this->authorizeResource('properties', 'update');

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'listing_type' => 'required|in:For Sale,For Rent',
            'property_type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'location' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'district' => 'nullable|string|max:100',
            'bedrooms' => 'required|integer|min:0',
            'bathrooms' => 'required|integer|min:0',
            'area' => 'required|numeric|min:0',
            'area_unit' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'features' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',

            // Translations
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required_with:translations|string|max:5',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.description' => 'nullable|string',
            'translations.*.location' => 'nullable|string|max:255',
            'translations.*.district' => 'nullable|string|max:100',
            'translations.*.features' => 'nullable|array',

            // Media
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery_image_ids' => 'nullable|array',
            'gallery_image_ids.*' => 'exists:media,id',

            // Meta data
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $property->update($validated);

        // Handle translations [[7]]
        if ($request->has('translations')) {
            foreach ($request->translations as $translation) {
                if (!empty($translation['locale'])) {
                    $property->setTranslations([
                        'title' => $translation['title'] ?? null,
                        'description' => $translation['description'] ?? null,
                        'location' => $translation['location'] ?? null,
                        'district' => $translation['district'] ?? null,
                        'features' => $translation['features'] ?? null,
                    ], $translation['locale']);
                }
            }
        }

        // Update primary image [[6]]
        if ($request->has('featured_image_id')) {
            $property->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Update gallery images [[6]]
        if ($request->has('gallery_image_ids')) {
            $property->syncMediaForZone($request->gallery_image_ids, 'gallery');
        }

        // Meta data is handled automatically by HasMetaData trait boot method [[9]]

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Property updated successfully!',
                'property' => $property->load('entityMedia.media', 'translations'),
            ]);
        }

        sweetalert()->success('Property updated successfully!');
        return redirect()->route('admin.properties.index');
    }

    /**
     * Handle translations for a property
     */
    public function translations(Request $request, Property $property)
    {
        $this->authorizeResource('properties', 'update');

        if ($request->isMethod('get')) {
            $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);
            $translations = [];

            foreach ($availableLocales as $locale) {
                $translations[$locale] = $property->getTranslations($locale);
                $translations[$locale]['completeness'] = $property->getTranslationCompleteness($locale);
            }

            return response()->json([
                'translations' => $translations,
                'translatable_fields' => $property->getTranslatableFields(),
                'available_locales' => $availableLocales,
            ]);
        }

        // Update translations
        $validated = $request->validate([
            'locale' => 'required|string|max:5',
            'translations' => 'required|array',
        ]);

        $property->setTranslations($validated['translations'], $validated['locale']);

        return response()->json([
            'success' => true,
            'message' => '✅ Translations updated successfully!',
            'completeness' => $property->getTranslationCompleteness($validated['locale']),
        ]);
    }

    /**
     * Duplicate a property with all its relationships
     */
    public function duplicate(Property $property)
    {
        $this->authorizeResource('properties', 'create');

        $newProperty = $property->replicate();
        $newProperty->title = $property->title . ' (Copy)';
        $newProperty->views = 0;
        $newProperty->save();

        // Copy translations [[7]]
        $newProperty->copyTranslationsFrom($property);

        // Copy media [[6]]
        foreach ($property->entityMedia as $media) {
            $newProperty->addMediaToZone($media->file_id, $media->zone);
        }

        // Copy metadata [[9]]
        if ($property->meta && $property->meta->exists) {
            $newMeta = $newProperty->meta()->create([]);

            // Copy metadata translations
            foreach ($property->meta->translations as $translation) {
                \App\Models\Translation::setTranslation(
                    \App\Models\MetaData::class,
                    $newMeta->id,
                    $translation->locale,
                    $translation->field,
                    $translation->value
                );
            }
        }

        sweetalert()->success('Property duplicated successfully!');
        return redirect()->route('admin.properties.edit', $newProperty);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property, Request $request)
    {
        $this->authorizeResource('properties', 'delete');

        // Soft delete will trigger trait boot methods to clean up related data
        $property->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ Property deleted successfully!',
            ]);
        }

        sweetalert()->success('Property deleted successfully!');
        return redirect()->route('admin.properties.index');
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $this->authorizeResource('properties', 'update');

        $validated = $request->validate([
            'action' => 'required|in:delete,feature,unfeature,available,unavailable',
            'ids' => 'required|array',
            'ids.*' => 'exists:properties,id',
        ]);

        $properties = Property::whereIn('id', $validated['ids'])->get();

        switch ($validated['action']) {
            case 'delete':
                $this->authorizeResource('properties', 'delete');
                foreach ($properties as $property) {
                    $property->delete();
                }
                $message = 'Properties deleted successfully!';
                break;

            case 'feature':
                Property::whereIn('id', $validated['ids'])->update(['is_featured' => true]);
                $message = 'Properties featured successfully!';
                break;

            case 'unfeature':
                Property::whereIn('id', $validated['ids'])->update(['is_featured' => false]);
                $message = 'Properties unfeatured successfully!';
                break;

            case 'available':
                Property::whereIn('id', $validated['ids'])->update(['is_available' => true]);
                $message = 'Properties marked as available!';
                break;

            case 'unavailable':
                Property::whereIn('id', $validated['ids'])->update(['is_available' => false]);
                $message = 'Properties marked as unavailable!';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Get serviced apartments (Apartments for rent)
     */
    public function servicedApartments(Request $request)
    {
        $this->authorizeResource('properties', 'view');

        if ($request->ajax()) {
            $query = Property::with(['user', 'entityMedia.media'])
                ->where('property_type', 'Apartment')
                ->where('listing_type', 'For Rent')
                ->where('is_available', true);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($property) {
                    $imageUrl = asset('images/no-image.png');
                    if ($property->relationLoaded('entityMedia') && $property->entityMedia->isNotEmpty()) {
                        $featuredMedia = $property->entityMedia->firstWhere('zone', 'primary_image');
                        if ($featuredMedia && $featuredMedia->media) {
                            $imageUrl = $featuredMedia->media->full_url;
                        }
                    }
                    return '<img src="'.$imageUrl.'" alt="Property" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
                })
                ->addColumn('title', function ($property) {
                    return '<strong>'.$property->title.'</strong><br>
                            <small class="text-muted">'.$property->location.'</small>';
                })
                ->addColumn('price', function ($property) {
                    return '<strong>$'.number_format($property->price).'/mo</strong>';
                })
                ->addColumn('details', function ($property) {
                    return '<i class="fas fa-bed"></i> '.$property->bedrooms.' &nbsp;
                            <i class="fas fa-bath"></i> '.$property->bathrooms.' &nbsp;
                            <i class="fas fa-ruler-combined"></i> '.$property->area.' '.$property->area_unit;
                })
                ->addColumn('status', function ($property) {
                    $badges = [];
                    if ($property->is_featured) {
                        $badges[] = '<span class="badge bg-warning text-dark">Featured</span>';
                    }
                    $badges[] = '<span class="badge bg-success">Available</span>';
                    return implode(' ', $badges);
                })
                ->addColumn('actions', function ($property) {
                    return '<a href="'.route('admin.properties.edit', $property->id).'" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="'.route('admin.properties.show', $property->id).'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                })
                ->rawColumns(['image', 'title', 'price', 'details', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.properties.serviced-apartments');
    }

    /**
     * Get boreys (gated community properties)
     */
    public function boreys(Request $request)
    {
        $this->authorizeResource('properties', 'view');

        if ($request->ajax()) {
            $query = Property::with(['user', 'entityMedia.media'])
                ->whereIn('property_type', ['House', 'Townhouse', 'Villa'])
                ->where(function($q) {
                    $q->where('title', 'like', '%borey%')
                      ->orWhere('title', 'like', '%Borey%')
                      ->orWhere('location', 'like', '%borey%')
                      ->orWhere('description', 'like', '%borey%')
                      ->orWhere('description', 'like', '%gated%');
                })
                ->where('is_available', true);

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($property) {
                    $imageUrl = asset('images/no-image.png');
                    if ($property->relationLoaded('entityMedia') && $property->entityMedia->isNotEmpty()) {
                        $featuredMedia = $property->entityMedia->firstWhere('zone', 'primary_image');
                        if ($featuredMedia && $featuredMedia->media) {
                            $imageUrl = $featuredMedia->media->full_url;
                        }
                    }
                    return '<img src="'.$imageUrl.'" alt="Property" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
                })
                ->addColumn('title', function ($property) {
                    return '<strong>'.$property->title.'</strong><br>
                            <small class="text-muted">'.$property->property_type.'</small>';
                })
                ->addColumn('listing', function ($property) {
                    $badgeClass = $property->listing_type === 'For Sale' ? 'bg-primary' : 'bg-info';
                    return '<span class="badge '.$badgeClass.'">'.$property->listing_type.'</span>';
                })
                ->addColumn('price', function ($property) {
                    return '<strong>$'.number_format($property->price).'</strong>';
                })
                ->addColumn('location', function ($property) {
                    return $property->city.'<br><small class="text-muted">'.$property->location.'</small>';
                })
                ->addColumn('details', function ($property) {
                    return '<i class="fas fa-bed"></i> '.$property->bedrooms.' &nbsp;
                            <i class="fas fa-bath"></i> '.$property->bathrooms;
                })
                ->addColumn('status', function ($property) {
                    $badges = [];
                    if ($property->is_featured) {
                        $badges[] = '<span class="badge bg-warning text-dark">Featured</span>';
                    }
                    $badges[] = '<span class="badge bg-success">Available</span>';
                    return implode(' ', $badges);
                })
                ->addColumn('actions', function ($property) {
                    return '<a href="'.route('admin.properties.edit', $property->id).'" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="'.route('admin.properties.show', $property->id).'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                })
                ->rawColumns(['image', 'title', 'listing', 'price', 'location', 'details', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.properties.boreys');
    }

    /**
     * Get luxury villas
     */
    public function luxuryVillas(Request $request)
    {
        $this->authorizeResource('properties', 'view');

        if ($request->ajax()) {
            $query = Property::with(['user', 'entityMedia.media'])
                ->where('property_type', 'Villa')
                ->where('is_available', true)
                ->orderBy('price', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($property) {
                    $imageUrl = asset('images/no-image.png');
                    if ($property->relationLoaded('entityMedia') && $property->entityMedia->isNotEmpty()) {
                        $featuredMedia = $property->entityMedia->firstWhere('zone', 'primary_image');
                        if ($featuredMedia && $featuredMedia->media) {
                            $imageUrl = $featuredMedia->media->full_url;
                        }
                    }
                    return '<img src="'.$imageUrl.'" alt="Property" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
                })
                ->addColumn('title', function ($property) {
                    return '<strong>'.$property->title.'</strong><br>
                            <small class="text-muted">'.$property->location.'</small>';
                })
                ->addColumn('listing', function ($property) {
                    $badgeClass = $property->listing_type === 'For Sale' ? 'bg-primary' : 'bg-info';
                    return '<span class="badge '.$badgeClass.'">'.$property->listing_type.'</span>';
                })
                ->addColumn('price', function ($property) {
                    $suffix = $property->listing_type === 'For Rent' ? '/mo' : '';
                    return '<strong class="text-primary">$'.number_format($property->price).$suffix.'</strong>';
                })
                ->addColumn('details', function ($property) {
                    return '<i class="fas fa-bed"></i> '.$property->bedrooms.' &nbsp;
                            <i class="fas fa-bath"></i> '.$property->bathrooms.' &nbsp;
                            <i class="fas fa-ruler-combined"></i> '.$property->area.' '.$property->area_unit;
                })
                ->addColumn('features', function ($property) {
                    if ($property->features && is_array($property->features)) {
                        return implode(', ', array_slice($property->features, 0, 3));
                    }
                    return '<span class="text-muted">No features</span>';
                })
                ->addColumn('status', function ($property) {
                    $badges = [];
                    if ($property->is_featured) {
                        $badges[] = '<span class="badge bg-warning text-dark">Featured</span>';
                    }
                    $badges[] = '<span class="badge bg-success">Available</span>';
                    return implode(' ', $badges);
                })
                ->addColumn('actions', function ($property) {
                    return '<a href="'.route('admin.properties.edit', $property->id).'" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="'.route('admin.properties.show', $property->id).'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                })
                ->rawColumns(['image', 'title', 'listing', 'price', 'details', 'features', 'status', 'actions'])
                ->make(true);
        }

        return view('admin.properties.luxury-villas');
    }

    /**
     * Get properties under market value (deals/featured at lower prices)
     */
    public function underMarketValue(Request $request)
    {
        $this->authorizeResource('properties', 'view');

        if ($request->ajax()) {
            $query = Property::with(['user', 'entityMedia.media'])
                ->where('is_available', true)
                ->where('is_featured', true)
                ->orderBy('price', 'asc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('image', function ($property) {
                    $imageUrl = asset('images/no-image.png');
                    if ($property->relationLoaded('entityMedia') && $property->entityMedia->isNotEmpty()) {
                        $featuredMedia = $property->entityMedia->firstWhere('zone', 'primary_image');
                        if ($featuredMedia && $featuredMedia->media) {
                            $imageUrl = $featuredMedia->media->full_url;
                        }
                    }
                    return '<img src="'.$imageUrl.'" alt="Property" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
                })
                ->addColumn('title', function ($property) {
                    return '<strong>'.$property->title.'</strong><br>
                            <small class="text-muted">'.$property->property_type.'</small>';
                })
                ->addColumn('listing', function ($property) {
                    $badgeClass = $property->listing_type === 'For Sale' ? 'bg-primary' : 'bg-info';
                    return '<span class="badge '.$badgeClass.'">'.$property->listing_type.'</span>';
                })
                ->addColumn('price', function ($property) {
                    return '<strong class="text-danger">$'.number_format($property->price).'</strong>
                            <br><span class="badge bg-danger">DEAL</span>';
                })
                ->addColumn('location', function ($property) {
                    return $property->city.'<br><small class="text-muted">'.$property->location.'</small>';
                })
                ->addColumn('details', function ($property) {
                    return '<i class="fas fa-bed"></i> '.$property->bedrooms.' &nbsp;
                            <i class="fas fa-bath"></i> '.$property->bathrooms.' &nbsp;
                            <i class="fas fa-ruler-combined"></i> '.$property->area.' '.$property->area_unit;
                })
                ->addColumn('actions', function ($property) {
                    return '<a href="'.route('admin.properties.edit', $property->id).'" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                            <a href="'.route('admin.properties.show', $property->id).'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                })
                ->rawColumns(['image', 'title', 'listing', 'price', 'location', 'details', 'actions'])
                ->make(true);
        }

        return view('admin.properties.under-market-value');
    }

    /**
     * Get property locations with counts for dashboard
     */
    public function locations(Request $request)
    {
        $this->authorizeResource('properties', 'view');

        $locations = Property::select('city')
            ->selectRaw('COUNT(*) as total_count')
            ->selectRaw('SUM(CASE WHEN is_available = 1 THEN 1 ELSE 0 END) as available_count')
            ->selectRaw('SUM(CASE WHEN listing_type = "For Sale" THEN 1 ELSE 0 END) as for_sale_count')
            ->selectRaw('SUM(CASE WHEN listing_type = "For Rent" THEN 1 ELSE 0 END) as for_rent_count')
            ->groupBy('city')
            ->orderByDesc('total_count')
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'locations' => $locations,
            ]);
        }

        return view('admin.properties.locations', compact('locations'));
    }

    /**
     * Get dashboard statistics for all property sections
     */
    public function dashboardStats()
    {
        $this->authorizeResource('properties', 'view');

        $stats = [
            'total_properties' => Property::count(),
            'available_properties' => Property::where('is_available', true)->count(),
            'featured_properties' => Property::where('is_featured', true)->count(),
            'for_sale' => Property::where('listing_type', 'For Sale')->where('is_available', true)->count(),
            'for_rent' => Property::where('listing_type', 'For Rent')->where('is_available', true)->count(),
            'serviced_apartments' => Property::where('property_type', 'Apartment')
                ->where('listing_type', 'For Rent')
                ->where('is_available', true)
                ->count(),
            'boreys' => Property::whereIn('property_type', ['House', 'Townhouse', 'Villa'])
                ->where(function($q) {
                    $q->where('title', 'like', '%borey%')
                      ->orWhere('location', 'like', '%borey%')
                      ->orWhere('description', 'like', '%borey%');
                })
                ->where('is_available', true)
                ->count(),
            'luxury_villas' => Property::where('property_type', 'Villa')
                ->where('is_available', true)
                ->count(),
            'under_market_value' => Property::where('is_featured', true)
                ->where('is_available', true)
                ->count(),
            'by_type' => Property::select('property_type')
                ->selectRaw('COUNT(*) as count')
                ->where('is_available', true)
                ->groupBy('property_type')
                ->pluck('count', 'property_type'),
            'by_city' => Property::select('city')
                ->selectRaw('COUNT(*) as count')
                ->where('is_available', true)
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(6)
                ->pluck('count', 'city'),
            'recent_properties' => Property::with(['entityMedia.media'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($p) {
                    return [
                        'id' => $p->id,
                        'title' => $p->title,
                        'price' => $p->formatted_price,
                        'listing_type' => $p->listing_type,
                        'property_type' => $p->property_type,
                        'primary_image' => $p->primary_image,
                        'created_at' => $p->created_at->diffForHumans(),
                    ];
                }),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
