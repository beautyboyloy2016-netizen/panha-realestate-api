<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use App\Models\PropertyType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    /**
     * Get all active property types.
     */
    public function propertyTypes(): JsonResponse
    {
        $propertyTypes = PropertyType::active()
            ->ordered()
            ->get(['id', 'name', 'slug', 'icon', 'description']);

        return response()->json([
            'success' => true,
            'data' => $propertyTypes,
        ]);
    }

    /**
     * Get all active features.
     */
    public function features(Request $request): JsonResponse
    {
        $query = Feature::active()->ordered();

        // Optionally filter by category
        if ($request->has('category')) {
            $query->category($request->category);
        }

        $features = $query->get(['id', 'name', 'slug', 'icon', 'category', 'description']);

        return response()->json([
            'success' => true,
            'data' => $features,
        ]);
    }

    /**
     * Get features grouped by category.
     */
    public function featuresGrouped(): JsonResponse
    {
        $features = Feature::getGroupedByCategory();

        return response()->json([
            'success' => true,
            'data' => $features,
        ]);
    }

    /**
     * Get all lookups in one request.
     */
    public function all(): JsonResponse
    {
        $propertyTypes = PropertyType::active()
            ->ordered()
            ->get(['id', 'name', 'slug', 'icon', 'description']);

        $features = Feature::active()
            ->ordered()
            ->get(['id', 'name', 'slug', 'icon', 'category', 'description']);

        $featuresGrouped = Feature::getGroupedByCategory();

        return response()->json([
            'success' => true,
            'data' => [
                'property_types' => $propertyTypes,
                'features' => $features,
                'features_grouped' => $featuresGrouped,
            ],
        ]);
    }
}
