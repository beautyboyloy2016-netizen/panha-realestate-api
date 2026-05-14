<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Display user's favorite properties.
     */
    public function index(Request $request)
    {
        // Will use auth()->user() with Sanctum
        $userId = 1; // Temporary

        $favorites = Favorite::with(['property.images'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate(15);

        return response()->json($favorites);
    }

    /**
     * Add property to favorites.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $userId = 1; // Temporary - replace with auth()->id()

        // Check if already favorited
        $exists = Favorite::where('user_id', $userId)
            ->where('property_id', $validated['property_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Property already in favorites'
            ], 409);
        }

        $favorite = Favorite::create([
            'user_id' => $userId,
            'property_id' => $validated['property_id'],
        ]);

        return response()->json([
            'message' => 'Property added to favorites',
            'favorite' => $favorite->load('property')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove property from favorites.
     */
    public function destroy($id)
    {
        $userId = 1; // Temporary - replace with auth()->id()

        $favorite = Favorite::where('user_id', $userId)
            ->where('property_id', $id)
            ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Favorite not found'
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Property removed from favorites'
        ]);
    }
}
