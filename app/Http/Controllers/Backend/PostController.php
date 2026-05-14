<?php

namespace App\Http\Controllers\Backend;

use App\Models\Post;
use App\Models\User;
use App\Models\PostTag;
use Illuminate\Support\Str;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class PostController extends BaseController
{
    protected string $resource = 'post';

    /**
     * Display a listing of posts.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $categories = PostCategory::active()->ordered()->get();
        $tags = PostTag::active()->ordered()->get();
        $users = User::all();

        return view('admin.posts.index', compact('categories', 'tags', 'users'));
    }

    /**
     * Show the form for creating a new post.
     */
    public function create()
    {
        $categories = PostCategory::active()->ordered()->get();
        $tags = PostTag::active()->ordered()->get();

        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * Get data for DataTables Ajax
     */
    private function getDataTableData(Request $request)
    {
        $locale = $request->get('locale', App::getLocale());

        $query = Post::with(['user', 'category', 'tags', 'entityMedia.media']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('image', function ($post) {
                $imageUrl = $post->primary_image ?? asset('images/no-image.png');
                return '<img src="'.$imageUrl.'" alt="'.$post->title.'" class="img-thumbnail" style="width:60px; height:40px; object-fit:cover;">';
            })
            ->addColumn('title', function ($post) use ($locale) {
                $title = $post->getTranslation('title', $locale) ?: $post->title;
                $slug = '<small class="text-muted d-block">'.$post->slug.'</small>';
                return '<strong>'.$title.'</strong>'.$slug;
            })
            ->addColumn('category', function ($post) {
                if ($post->category) {
                    $color = $post->category->color ?? '#6c757d';
                    return '<span class="badge" style="background-color: '.$color.'">'.$post->category->name.'</span>';
                }
                return '<span class="text-muted">Uncategorized</span>';
            })
            ->addColumn('tags', function ($post) {
                return $post->tags->take(3)->map(function ($tag) {
                    $color = $tag->color ?? '#6c757d';
                    return '<span class="badge" style="background-color: '.$color.'; font-size: 10px;">'.$tag->name.'</span>';
                })->join(' ') . ($post->tags->count() > 3 ? ' <span class="badge bg-secondary">+'.($post->tags->count() - 3).'</span>' : '');
            })
            ->addColumn('author', function ($post) {
                return $post->user ? $post->user->name : '<span class="text-muted">N/A</span>';
            })
            ->addColumn('status', function ($post) {
                $badges = [
                    'published' => 'bg-success',
                    'draft' => 'bg-warning',
                    'scheduled' => 'bg-info',
                    'archived' => 'bg-secondary',
                ];
                $class = $badges[$post->status] ?? 'bg-secondary';
                return '<span class="badge '.$class.'">'.ucfirst($post->status).'</span>';
            })
            ->addColumn('views', function ($post) {
                return '<i class="fas fa-eye text-muted"></i> '.number_format($post->views);
            })
            ->addColumn('published_at', function ($post) {
                return $post->published_at ? $post->published_at->format('M d, Y') : '-';
            })
            ->addColumn('actions', function ($post) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-post" data-id="'.$post->id.'" title="View">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-post" data-id="'.$post->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-post" data-id="'.$post->id.'" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', $keyword);
            })
            ->filterColumn('category', function ($query, $keyword) {
                $query->whereHas('category', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })
            ->rawColumns(['image', 'title', 'category', 'tags', 'author', 'status', 'views', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created post.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug',
            'category_id' => 'nullable|exists:post_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled,archived',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:post_tags,id',
            'featured_image_id' => 'nullable|exists:media,id',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['allow_comments'] = $request->boolean('allow_comments', true);

        if ($validated['status'] === 'published' && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post = Post::create($validated);

        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        }

        // Handle featured image
        if ($request->has('featured_image_id')) {
            $post->setMediaForZone($request->featured_image_id, 'featured_image');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Post created successfully!',
                'post' => $post->load(['category', 'tags', 'user']),
            ]);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully!');
    }

    /**
     * Display the specified post.
     */
    public function show(Post $post, Request $request)
    {
        $post->load(['user', 'category', 'tags', 'entityMedia.media']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'post' => $post,
            ]);
        }

        return view('admin.posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified post.
     */
    public function edit(Post $post, Request $request)
    {
        $post->load(['category', 'tags', 'entityMedia.media']);

        if ($request->ajax()) {
            $postData = $post->toArray();
            $postData['tag_ids'] = $post->tags->pluck('id');

            // Get featured image
            $featuredMedia = $post->entityMedia->firstWhere('zone', 'featured_image');
            $postData['featured_image'] = $featuredMedia && $featuredMedia->media ? [
                'id' => $featuredMedia->media->id,
                'full_url' => $featuredMedia->media->full_url,
                'original_name' => $featuredMedia->media->original_name,
            ] : null;

            return response()->json([
                'success' => true,
                'post' => $postData,
            ]);
        }

        $categories = PostCategory::active()->ordered()->get();
        $tags = PostTag::active()->ordered()->get();

        return view('admin.posts.edit', compact('post', 'categories', 'tags'));
    }

    /**
     * Update the specified post.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:posts,slug,'.$post->id,
            'category_id' => 'nullable|exists:post_categories,id',
            'excerpt' => 'nullable|string',
            'content' => 'nullable|string',
            'status' => 'required|in:draft,published,scheduled,archived',
            'is_featured' => 'boolean',
            'allow_comments' => 'boolean',
            'published_at' => 'nullable|date',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:post_tags,id',
            'featured_image_id' => 'nullable|exists:media,id',
        ]);

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['allow_comments'] = $request->boolean('allow_comments', true);

        if ($validated['status'] === 'published' && empty($post->published_at) && empty($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $post->update($validated);

        // Sync tags
        if ($request->has('tags')) {
            $post->tags()->sync($request->tags);
        } else {
            $post->tags()->sync([]);
        }

        // Handle featured image
        if ($request->has('featured_image_id')) {
            $post->setMediaForZone($request->featured_image_id, 'featured_image');
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully!',
                'post' => $post->load(['category', 'tags', 'user']),
            ]);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully!');
    }

    /**
     * Remove the specified post.
     */
    public function destroy(Post $post, Request $request)
    {
        $post->tags()->detach();
        $post->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully!',
            ]);
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully!');
    }
}
