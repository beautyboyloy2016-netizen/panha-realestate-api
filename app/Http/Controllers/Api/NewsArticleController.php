<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NewsArticle;
use Illuminate\Http\Request;

class NewsArticleController extends Controller
{
  /**
   * Display a listing of news articles with filtering and pagination.
   */
  public function index(Request $request)
  {
    $query = NewsArticle::with(['entityMedia.media']);

    // Filter by category
    if ($request->has('category')) {
      $query->where('category', $request->category);
    }

    // Search
    if ($request->has('search')) {
      $search = $request->search;
      $query->where(function ($q) use ($search) {
        $q->where('title', 'like', "%{$search}%")
          ->orWhere('excerpt', 'like', "%{$search}%")
          ->orWhere('content', 'like', "%{$search}%");
      });
    }

    // Sorting
    $sortBy = $request->get('sort_by', 'published_at');
    $sortOrder = $request->get('sort_order', 'desc');

    $allowedSorts = ['title', 'published_at', 'created_at'];
    if (in_array($sortBy, $allowedSorts)) {
      $query->orderBy($sortBy, $sortOrder);
    }

    // Pagination
    $perPage = $request->get('per_page', 15);
    $articles = $query->paginate($perPage);

    return response()->json($articles);
  }

  /**
   * Store a newly created news article.
   */
  public function store(Request $request)
  {
    $validated = $request->validate([
      'title' => 'required|string|max:255',
      'category' => 'required|string|max:100',
      'excerpt' => 'required|string',
      'content' => 'nullable|string',
      'published_at' => 'nullable|date',
      'featured_image_id' => 'nullable|integer|exists:media,id',
    ]);

    $article = NewsArticle::create($validated);

    // Set featured image using HasMedia trait
    if ($request->has('featured_image_id')) {
      $article->setMediaForZone($request->featured_image_id, 'featured_image');
    }

    return response()->json([
      'message' => 'News article created successfully',
      'article' => $article->load('entityMedia.media')
    ], 201);
  }

  /**
   * Display the specified news article.
   */
  public function show(NewsArticle $article)
  {
    $article->load('entityMedia.media');

    return response()->json([
      'article' => $article
    ]);
  }

  /**
   * Update the specified news article.
   */
  public function update(Request $request, NewsArticle $article)
  {
    $validated = $request->validate([
      'title' => 'sometimes|string|max:255',
      'category' => 'sometimes|string|max:100',
      'excerpt' => 'sometimes|string',
      'content' => 'nullable|string',
      'published_at' => 'nullable|date',
      'featured_image_id' => 'nullable|integer|exists:media,id',
    ]);

    $article->update($validated);

    // Update featured image using HasMedia trait
    if ($request->has('featured_image_id')) {
      $article->setMediaForZone($request->featured_image_id, 'featured_image');
    }

    return response()->json([
      'message' => 'News article updated successfully',
      'article' => $article->load('entityMedia.media')
    ]);
  }

  /**
   * Remove the specified news article.
   */
  public function destroy(NewsArticle $article)
  {
    $article->delete();

    return response()->json([
      'message' => 'News article deleted successfully'
    ]);
  }

  /**
   * Get unique categories.
   */
  public function categories()
  {
    $categories = NewsArticle::select('category')
      ->distinct()
      ->orderBy('category')
      ->pluck('category');

    return response()->json($categories);
  }

  /**
   * Get latest news articles.
   */
  public function latest()
  {
    $articles = NewsArticle::with(['entityMedia.media'])
      ->orderBy('published_at', 'desc')
      ->limit(5)
      ->get();

    return response()->json($articles);
  }
}
