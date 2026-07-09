<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects with filtering and pagination.
     */
    public function index(Request $request)
    {
        $query = Project::with(['entityMedia.media']);

        // Filter by featured
        if ($request->has('featured')) {
            $query->where('featured', $request->boolean('featured'));
        }

        // Filter by location
        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        // Filter by developer
        if ($request->has('developer')) {
            $query->where('developer', $request->developer);
        }

        // Filter by price range
        if ($request->has('min_price')) {
            $query->where('price_from', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price_from', '<=', $request->max_price);
        }

        // Filter by rental yield
        if ($request->has('min_yield')) {
            $query->where('rental_yield', '>=', $request->min_yield);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = strtolower((string) $request->get('sort_order', 'desc')) === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['name', 'price_from', 'units', 'rental_yield', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min(max((int) $request->get('per_page', 15), 1), 50);
        $projects = $query->paginate($perPage);

        return response()->json($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
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
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'integer|exists:media,id',
        ]);

        $project = Project::create($validated);

        // Set primary image using HasMedia trait
        if ($request->has('featured_image_id')) {
            $project->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Set gallery images using HasMedia trait
        if ($request->has('gallery_ids')) {
            $project->syncMediaForZone($request->gallery_ids, 'gallery');
        }

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project->load('entityMedia.media')
        ], 201);
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $project->load('entityMedia.media');

        return response()->json([
            'project' => $project
        ]);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'developer' => 'sometimes|string|max:255',
            'units' => 'sometimes|integer|min:1',
            'price_from' => 'sometimes|string',
            'completion' => 'sometimes|string',
            'featured' => 'boolean',
            'rental_yield' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'featured_image_id' => 'nullable|integer|exists:media,id',
            'gallery_ids' => 'nullable|array',
            'gallery_ids.*' => 'integer|exists:media,id',
        ]);

        $project->update($validated);

        // Update primary image using HasMedia trait
        if ($request->has('featured_image_id')) {
            $project->setMediaForZone($request->featured_image_id, 'primary_image');
        }

        // Update gallery images using HasMedia trait
        if ($request->has('gallery_ids')) {
            $project->syncMediaForZone($request->gallery_ids, 'gallery');
        }

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project->load('entityMedia.media')
        ]);
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json([
            'message' => 'Project deleted successfully'
        ]);
    }

    /**
     * Get featured projects.
     */
    public function featured()
    {
        $projects = Project::with(['entityMedia.media'])
            ->where('featured', true)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($projects);
    }

    /**
     * Get high yield projects.
     */
    public function highYield()
    {
        $projects = Project::with(['entityMedia.media'])
            ->whereNotNull('rental_yield')
            ->where('rental_yield', '>', 0)
            ->orderBy('rental_yield', 'desc')
            ->get();

        return response()->json($projects);
    }
}
