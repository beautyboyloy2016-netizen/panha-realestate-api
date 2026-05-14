<?php

namespace App\Http\Controllers\Backend;

use App\Models\NewsArticle;
use App\Traits\HasPermissions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Yajra\DataTables\Facades\DataTables;

class NewsArticleController extends BaseController
{
    use HasPermissions;

    protected string $resource = 'news_article';

    /**
     * Display a listing of news articles.
     */
    public function index(Request $request)
    {
        // Check permissions
        $this->authorizeResource('news_articles', 'view');

        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $categories = NewsArticle::select('category')->distinct()->pluck('category');
        return view('admin.news-articles.index', compact('categories'));
    }

    /**
     * Get data for DataTables Ajax using Yajra DataTables
     */
    private function getDataTableData(Request $request)
    {
        $locale = $request->get('locale', App::getLocale());

        $query = NewsArticle::with(['entityMedia.media']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('image', function ($article) {
                // Only use HasMedia trait (entityMedia) - no legacy fallback
                $imageUrl = asset('images/no-image.png');
                if ($article->relationLoaded('entityMedia') && $article->entityMedia->isNotEmpty()) {
                    $featuredMedia = $article->entityMedia->firstWhere('zone', 'featured_image');
                    if ($featuredMedia && $featuredMedia->media) {
                        $imageUrl = $featuredMedia->media->full_url;
                    }
                }

                return '<img src="'.$imageUrl.'" alt="Article" class="img-thumbnail" style="width:80px; height:60px; object-fit:cover;">';
            })
            ->addColumn('title', function ($article) use ($locale) {
                // Using HasTranslations trait to get translated title [[11]]
                $title = $article->getTranslation('title', $locale);
                $excerpt = $article->getTranslation('excerpt', $locale);
                $displayExcerpt = $excerpt ? substr($excerpt, 0, 50).'...' : 'No excerpt';
                return '<strong>'.$title.'</strong><br>
                        <small class="text-muted">'.$displayExcerpt.'</small>';
            })
            ->addColumn('category', function ($article) use ($locale) {
                // Using HasTranslations for category [[11]]
                $category = $article->getTranslation('category', $locale);
                return '<span class="badge bg-primary">'.$category.'</span>';
            })
            ->addColumn('published', function ($article) {
                if ($article->published_at) {
                    $date = \Carbon\Carbon::parse($article->published_at);
                    $statusBadge = $date->isFuture()
                        ? '<span class="badge bg-info">Scheduled</span>'
                        : '<span class="badge bg-success">Published</span>';
                    return $date->format('Y-m-d').'<br><small class="text-muted">'.$date->diffForHumans().'</small><br>'.$statusBadge;
                }
                return '<span class="badge bg-warning text-dark">Draft</span>';
            })
            ->addColumn('status', function ($article) {
                $badges = [];

                if ($article->published_at) {
                    $date = \Carbon\Carbon::parse($article->published_at);
                    if ($date->isPast()) {
                        $badges[] = '<span class="badge bg-success">Published</span>';
                    } else {
                        $badges[] = '<span class="badge bg-info">Scheduled</span>';
                    }
                } else {
                    $badges[] = '<span class="badge bg-secondary">Draft</span>';
                }

                // Show translation completeness [[11]]
                $completeness = $article->getTranslationCompleteness();
                if ($completeness < 100) {
                    $badges[] = '<span class="badge bg-warning" title="Translation Completeness">'.round($completeness).'%</span>';
                }

                return implode(' ', $badges);
            })
            ->addColumn('actions', function ($article) {
                $actions = [];
                $showUrl = route('admin.news-articles.show', $article->id);

                // Check permissions for each action [[13]]
                $actions[] = '<a href="'.$showUrl.'" class="btn btn-sm btn-info" title="View">
                    <i class="fas fa-eye"></i>
                </a>';

                if ($this->canCRUD('news_article', 'edit')) {
                    $editUrl = route('admin.news-articles.edit', $article->id);
                    $actions[] = '<a href="'.$editUrl.'" class="btn btn-sm btn-warning" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>';
                    $actions[] = '<button class="btn btn-sm btn-primary translate-article" data-id="'.$article->id.'" title="Translations">
                        <i class="fas fa-language"></i>
                    </button>';
                }

                if ($this->canCRUD('news_article', 'delete')) {
                    $actions[] = '<button class="btn btn-sm btn-danger delete-article" data-id="'.$article->id.'" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>';
                }

                return '<div class="btn-group">' . implode('', $actions) . '</div>';
            })
            ->editColumn('created_at', function ($article) {
                return $article->created_at->format('Y-m-d H:i');
            })
            ->filterColumn('title', function ($query, $keyword) {
                $query->where('title', 'like', "%{$keyword}%");
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->where('category', 'like', "%{$keyword}%");
            })
            ->rawColumns(['image', 'title', 'category', 'published', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorizeResource('news_articles', 'create');

        $categories = NewsArticle::select('category')->distinct()->pluck('category');
        $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);

        return view('admin.news-articles.create', compact('categories', 'availableLocales'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorizeResource('news_articles', 'create');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'image_url' => 'nullable|string',
            'published_at' => 'nullable|date',

            // Translations [[11]]
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required_with:translations|string|max:5',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.category' => 'nullable|string|max:100',
            'translations.*.excerpt' => 'nullable|string',
            'translations.*.content' => 'nullable|string',

            // Media [[12]]
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery_image_ids' => 'nullable|array',
            'gallery_image_ids.*' => 'exists:media,id',

            // Meta data [[14]]
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $article = NewsArticle::create($validated);

        // Handle translations [[11]]
        if ($request->has('translations')) {
            foreach ($request->translations as $translation) {
                if (!empty($translation['locale'])) {
                    $article->setTranslations([
                        'title' => $translation['title'] ?? null,
                        'category' => $translation['category'] ?? null,
                        'excerpt' => $translation['excerpt'] ?? null,
                        'content' => $translation['content'] ?? null,
                    ], $translation['locale']);
                }
            }
        }

        // Handle featured image using HasMedia trait [[12]]
        if ($request->has('featured_image_id')) {
            $article->setMediaForZone($request->featured_image_id, 'featured_image');
        }

        // Handle gallery images [[12]]
        if ($request->has('gallery_image_ids')) {
            $article->syncMediaForZone($request->gallery_image_ids, 'gallery');
        }

        // Meta data is handled automatically by HasMetaData trait boot method [[14]]

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ News article created successfully!',
                'article' => $article->load('entityMedia.media', 'translations'),
            ]);
        }

        sweetalert()->success('News article created successfully!');
        return redirect()->route('admin.news-articles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'view');

        $newsArticle->load(['entityMedia.media', 'translations', 'meta']);

        // Get all available translations
        $availableLocales = $newsArticle->getAvailableLocales();
        $translationStatus = $newsArticle->getTranslationStatus();

        return view('admin.news-articles.show', compact('newsArticle', 'availableLocales', 'translationStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'update');

        $newsArticle->load(['entityMedia.media', 'translations', 'meta']);

        if ($request->ajax()) {
            // Include translations for all locales
            $articleData = $newsArticle->toArray();
            $articleData['all_translations'] = $newsArticle->withAllTranslations();

            // Get featured image from loaded relationship
            $featuredEntityMedia = $newsArticle->entityMedia->firstWhere('zone', 'featured_image');
            $articleData['featured_image'] = $featuredEntityMedia && $featuredEntityMedia->media ? [
                'id' => $featuredEntityMedia->media->id,
                'full_url' => $featuredEntityMedia->media->full_url,
                'file_url' => $featuredEntityMedia->media->file_url ?? $featuredEntityMedia->media->full_url,
                'original_name' => $featuredEntityMedia->media->original_name,
                'file_name' => $featuredEntityMedia->media->file_name,
                'file_size' => $featuredEntityMedia->media->file_size,
                'mime_type' => $featuredEntityMedia->media->mime_type,
            ] : null;

            // Get gallery images from loaded relationship
            $galleryEntityMedia = $newsArticle->entityMedia->where('zone', 'gallery');
            $articleData['gallery_images'] = $galleryEntityMedia->map(function ($em) {
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

            return response()->json($articleData);
        }

        $categories = NewsArticle::select('category')->distinct()->pluck('category');
        $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);

        return view('admin.news-articles.edit', compact('newsArticle', 'categories', 'availableLocales'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'update');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'excerpt' => 'required|string',
            'content' => 'required|string',
            'published_at' => 'nullable|date',

            // Translations
            'translations' => 'nullable|array',
            'translations.*.locale' => 'required_with:translations|string|max:5',
            'translations.*.title' => 'nullable|string|max:255',
            'translations.*.category' => 'nullable|string|max:100',
            'translations.*.excerpt' => 'nullable|string',
            'translations.*.content' => 'nullable|string',

            // Media
            'featured_image_id' => 'nullable|exists:media,id',
            'gallery_image_ids' => 'nullable|array',
            'gallery_image_ids.*' => 'exists:media,id',

            // Meta data
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $newsArticle->update($validated);

        // Handle translations [[11]]
        if ($request->has('translations')) {
            foreach ($request->translations as $translation) {
                if (!empty($translation['locale'])) {
                    $newsArticle->setTranslations([
                        'title' => $translation['title'] ?? null,
                        'category' => $translation['category'] ?? null,
                        'excerpt' => $translation['excerpt'] ?? null,
                        'content' => $translation['content'] ?? null,
                    ], $translation['locale']);
                }
            }
        }

        // Update featured image [[12]]
        if ($request->has('featured_image_id')) {
            $newsArticle->setMediaForZone($request->featured_image_id, 'featured_image');
        }

        // Update gallery images [[12]]
        if ($request->has('gallery_image_ids')) {
            $newsArticle->syncMediaForZone($request->gallery_image_ids, 'gallery');
        }

        // Meta data is handled automatically by HasMetaData trait boot method [[14]]

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ News article updated successfully!',
                'article' => $newsArticle->load('entityMedia.media', 'translations'),
            ]);
        }

        sweetalert()->success('News article updated successfully!');
        return redirect()->route('admin.news-articles.index');
    }

    /**
     * Handle translations for an article
     */
    public function translations(Request $request, NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'update');

        if ($request->isMethod('get')) {
            $availableLocales = config('app.available_locales', ['en', 'km', 'zh']);
            $translations = [];

            foreach ($availableLocales as $locale) {
                $translations[$locale] = $newsArticle->getTranslations($locale);
                $translations[$locale]['completeness'] = $newsArticle->getTranslationCompleteness($locale);
            }

            return response()->json([
                'translations' => $translations,
                'translatable_fields' => $newsArticle->getTranslatableFields(),
                'available_locales' => $availableLocales,
            ]);
        }

        // Update translations
        $validated = $request->validate([
            'locale' => 'required|string|max:5',
            'translations' => 'required|array',
        ]);

        $newsArticle->setTranslations($validated['translations'], $validated['locale']);

        return response()->json([
            'success' => true,
            'message' => '✅ Translations updated successfully!',
            'completeness' => $newsArticle->getTranslationCompleteness($validated['locale']),
        ]);
    }

    /**
     * Duplicate an article with all its relationships
     */
    public function duplicate(NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'create');

        $newArticle = $newsArticle->replicate();
        $newArticle->title = $newsArticle->title . ' (Copy)';
        $newArticle->published_at = null; // Reset to draft
        $newArticle->save();

        // Copy translations [[11]]
        $newArticle->copyTranslationsFrom($newsArticle);

        // Copy media [[12]]
        foreach ($newsArticle->entityMedia as $media) {
            $newArticle->addMediaToZone($media->media_id, $media->zone);
        }

        // Copy metadata [[14]]
        if ($newsArticle->meta && $newsArticle->meta->exists) {
            $newMeta = $newArticle->meta()->create([]);

            // Copy metadata translations
            foreach ($newsArticle->meta->translations as $translation) {
                \App\Models\Translation::setTranslation(
                    \App\Models\MetaData::class,
                    $newMeta->id,
                    $translation->locale,
                    $translation->field,
                    $translation->value
                );
            }
        }

        sweetalert()->success('Article duplicated successfully!');
        return redirect()->route('admin.news-articles.edit', $newArticle);
    }

    /**
     * Toggle publish status
     */
    public function togglePublish(NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'update');

        if ($newsArticle->published_at) {
            $newsArticle->update(['published_at' => null]);
            $message = 'Article unpublished!';
        } else {
            $newsArticle->update(['published_at' => now()]);
            $message = 'Article published!';
        }

        return response()->json([
            'success' => true,
            'published' => $newsArticle->published_at !== null,
            'message' => $message,
        ]);
    }

    /**
     * Schedule article publication
     */
    public function schedule(Request $request, NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'update');

        $validated = $request->validate([
            'published_at' => 'required|date|after:now',
        ]);

        $newsArticle->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Article scheduled for publication!',
            'published_at' => $newsArticle->published_at,
        ]);
    }

    /**
     * Bulk operations
     */
    public function bulkAction(Request $request)
    {
        $this->authorizeResource('news_articles', 'update');

        $validated = $request->validate([
            'action' => 'required|in:delete,publish,unpublish,change_category',
            'ids' => 'required|array',
            'ids.*' => 'exists:news_articles,id',
            'category' => 'required_if:action,change_category|string|max:100',
        ]);

        $articles = NewsArticle::whereIn('id', $validated['ids'])->get();

        switch ($validated['action']) {
            case 'delete':
                $this->authorizeResource('news_articles', 'delete');
                foreach ($articles as $article) {
                    $article->delete();
                }
                $message = 'Articles deleted successfully!';
                break;

            case 'publish':
                NewsArticle::whereIn('id', $validated['ids'])->update(['published_at' => now()]);
                $message = 'Articles published successfully!';
                break;

            case 'unpublish':
                NewsArticle::whereIn('id', $validated['ids'])->update(['published_at' => null]);
                $message = 'Articles unpublished successfully!';
                break;

            case 'change_category':
                NewsArticle::whereIn('id', $validated['ids'])->update(['category' => $validated['category']]);
                $message = 'Category updated successfully!';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    /**
     * Get article preview in different locale
     */
    public function preview(Request $request, NewsArticle $newsArticle)
    {
        $this->authorizeResource('news_articles', 'view');

        $locale = $request->get('locale', App::getLocale());
        $translatedArticle = $newsArticle->translateTo($locale);

        return response()->json([
            'success' => true,
            'article' => $translatedArticle->toArray(),
            'locale' => $locale,
            'completeness' => $newsArticle->getTranslationCompleteness($locale),
        ]);
    }

    /**
     * Export articles data
     */
    public function export(Request $request)
    {
        $this->authorizeResource('news_articles', 'view');

        $locale = $request->get('locale', App::getLocale());
        $category = $request->get('category');

        $query = NewsArticle::query();

        if ($category) {
            $query->where('category', $category);
        }

        $articles = $query->get()->map(function ($article) use ($locale) {
            return $article->translateTo($locale);
        });

        // Implementation depends on your export package
        // Example: return Excel::download(new ArticlesExport($articles), 'articles.xlsx');

        return response()->json([
            'success' => true,
            'message' => 'Export functionality to be implemented',
            'data' => $articles,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewsArticle $newsArticle, Request $request)
    {
        $this->authorizeResource('news_articles', 'delete');

        // Soft delete will trigger trait boot methods to clean up related data
        $newsArticle->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ News article deleted successfully!',
            ]);
        }

        sweetalert()->success('News article deleted successfully!');
        return redirect()->route('admin.news-articles.index');
    }
}
