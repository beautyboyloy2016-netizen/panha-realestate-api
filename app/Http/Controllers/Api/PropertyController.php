<?php

namespace App\Http\Controllers\Api;

use App\Models\Property;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    /**
     * Display a listing of properties with filtering and pagination.
     */
    public function index(Request $request)
    {
        $query = Property::with(['entityMedia.media', 'user'])->available();

        // Filtering
        if ($request->has('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->has('property_type')) {
            $propertyTypes = explode(',', $request->property_type);
            $query->whereIn('property_type', $propertyTypes);
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        if ($request->has('min_price') || $request->has('max_price')) {
            $query->priceRange($request->min_price, $request->max_price);
        }

        if ($request->has('min_bedrooms')) {
            $query->where('bedrooms', '>=', $request->min_bedrooms);
        }

        if ($request->has('bathrooms')) {
            $query->where('bathrooms', '>=', $request->bathrooms);
        }

        if ($request->has('min_area') || $request->has('max_area')) {
            $query->areaRange($request->min_area, $request->max_area);
        }

        if ($request->has('is_featured')) {
            $query->featured();
        }

        // Filter by features (e.g., Swimming Pool, Garden, Parking)
        if ($request->has('features')) {
            $features = is_array($request->features) ? $request->features : explode(',', $request->features);
            foreach ($features as $feature) {
                $query->whereJsonContains('features', trim($feature));
            }
        }

        // Search by title or location
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $allowedSorts = ['price', 'area', 'bedrooms', 'created_at', 'views'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $properties = $query->paginate($perPage);

        return response()->json($properties);
    }

    /**
     * Store a newly created property.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'listing_type' => 'required|in:For Sale,For Rent',
            'property_type' => 'required|in:House,Apartment,Condo,Villa,Townhouse,Land,Commercial',
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
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'integer|exists:media,id',
        ]);

        // Get authenticated user
        $validated['user_id'] = Auth::id() ?? 1;

        $property = Property::create($validated);

        // Set primary image using HasMedia trait
        if ($request->has('featured_image_id')) {
            $property->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Set gallery images using HasMedia trait
        if ($request->has('gallery_ids')) {
            $property->syncMediaForZone($request->gallery_ids, 'gallery');
        }

        return response()->json([
            'message' => 'Property created successfully',
            'property' => $property->load('entityMedia.media')
        ], 201);
    }

    /**
     * Display the specified property.
     */
    public function show(Property $property)
    {
        $property->incrementViews();
        $property->load(['entityMedia.media', 'user']);

        return response()->json([
            'property' => $property
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Property $property)
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'listing_type' => 'sometimes|in:For Sale,For Rent',
            'property_type' => 'sometimes|in:House,Apartment,Condo,Villa,Townhouse,Land,Commercial',
            'price' => 'sometimes|numeric|min:0',
            'location' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:100',
            'district' => 'nullable|string|max:100',
            'bedrooms' => 'sometimes|integer|min:0',
            'bathrooms' => 'sometimes|integer|min:0',
            'area' => 'sometimes|numeric|min:0',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'features' => 'nullable|array',
            'is_featured' => 'boolean',
            'is_available' => 'boolean',
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'integer|exists:media,id',
        ]);

        $property->update($validated);

        // Update primary image using HasMedia trait
        if ($request->has('featured_image_id')) {
            $property->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Update gallery images using HasMedia trait
        if ($request->has('gallery_ids')) {
            $property->syncMediaForZone($request->gallery_ids, 'gallery');
        }

        return response()->json([
            'message' => 'Property updated successfully',
            'property' => $property->load('entityMedia.media')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Property $property)
    {
        $property->delete();

        return response()->json([
            'message' => 'Property deleted successfully'
        ]);
    }

    /**
     * Get featured properties.
     */
    public function featured()
    {
        $properties = Property::with(['entityMedia.media'])
            ->featured()
            ->available()
            ->limit(9)
            ->get();

        return response()->json($properties);
    }

    /**
     * Get properties for sale.
     */
    public function forSale(Request $request)
    {
        $query = Property::with(['entityMedia.media'])->forSale()->available();

        $perPage = $request->get('per_page', 15);
        $properties = $query->paginate($perPage);

        return response()->json($properties);
    }

    /**
     * Get properties for rent.
     */
    public function forRent(Request $request)
    {
        $query = Property::with(['entityMedia.media'])->forRent()->available();

        $perPage = $request->get('per_page', 15);
        $properties = $query->paginate($perPage);

        return response()->json($properties);
    }

    /**
     * Get serviced apartments (Apartments for rent with premium features).
     */
    public function servicedApartments(Request $request)
    {
        $query = Property::with(['entityMedia.media'])
            ->where('property_type', 'Apartment')
            ->forRent()
            ->available()
            ->orderBy('created_at', 'desc');

        $limit = $request->get('limit', 4);
        $properties = $query->limit($limit)->get();

        return response()->json($properties);
    }

    /**
     * Get boreys (gated community properties - typically House or Townhouse types).
     */
    public function boreys(Request $request)
    {
        $query = Property::with(['entityMedia.media'])
            ->whereIn('property_type', ['House', 'Townhouse', 'Villa'])
            ->where(function($q) {
                $q->where('title', 'like', '%borey%')
                  ->orWhere('title', 'like', '%Borey%')
                  ->orWhere('location', 'like', '%borey%')
                  ->orWhere('description', 'like', '%borey%')
                  ->orWhere('description', 'like', '%gated%');
            })
            ->available()
            ->orderBy('created_at', 'desc');

        $limit = $request->get('limit', 4);
        $properties = $query->limit($limit)->get();

        return response()->json($properties);
    }

    /**
     * Get luxury villas.
     */
    public function luxuryVillas(Request $request)
    {
        $query = Property::with(['entityMedia.media'])
            ->where('property_type', 'Villa')
            ->available()
            ->orderBy('price', 'desc');

        $limit = $request->get('limit', 3);
        $properties = $query->limit($limit)->get();

        return response()->json($properties);
    }

    /**
     * Get properties under market value (discounted/special deals).
     * For now, returns properties marked as featured with lower prices.
     */
    public function underMarketValue(Request $request)
    {
        $query = Property::with(['entityMedia.media'])
            ->available()
            ->where('is_featured', true)
            ->orderBy('price', 'asc');

        $limit = $request->get('limit', 3);
        $properties = $query->limit($limit)->get();

        return response()->json($properties);
    }

    /**
     * Get popular locations with property counts.
     */
    public function locations()
    {
        $locations = Property::select('city')
            ->selectRaw('COUNT(*) as count')
            ->available()
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->city,
                    'count' => $item->count,
                ];
            });

        return response()->json($locations);
    }
}
