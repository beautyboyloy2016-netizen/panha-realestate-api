<?php

namespace App\Http\Controllers\Backend;

use App\Models\PostTag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PostTagController extends BaseController
{
    protected string $resource = 'post_tag';

    /**
     * Display a listing of tags.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        return view('admin.post-tags.index');
    }

    /**
     * Get data for DataTables Ajax
     */
    private function getDataTableData(Request $request)
    {
        $query = PostTag::withCount('posts');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', function ($tag) {
                $color = $tag->color ?? '#6c757d';
                return '<span class="badge" style="background-color: '.$color.'; font-size: 14px;">'.$tag->name.'</span>';
            })
            ->addColumn('slug', function ($tag) {
                return '<code>'.$tag->slug.'</code>';
            })
            ->addColumn('posts_count', function ($tag) {
                return '<span class="badge bg-info">'.$tag->posts_count.' posts</span>';
            })
            ->addColumn('status', function ($tag) {
                return $tag->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($tag) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-warning edit-tag" data-id="'.$tag->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-tag" data-id="'.$tag->id.'" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->filterColumn('status', function ($query, $keyword) {
                if ($keyword === 'Active') {
                    $query->where('is_active', 1);
                } elseif ($keyword === 'Inactive') {
                    $query->where('is_active', 0);
                }
            })
            ->rawColumns(['name', 'slug', 'posts_count', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created tag.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_tags,slug',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        $tag = PostTag::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag created successfully!',
                'tag' => $tag,
            ]);
        }

        return redirect()->route('admin.post-tags.index')->with('success', 'Tag created successfully!');
    }

    /**
     * Show the form for editing the specified tag.
     */
    public function edit(PostTag $postTag, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'tag' => $postTag,
            ]);
        }

        return view('admin.post-tags.edit', compact('postTag'));
    }

    /**
     * Update the specified tag.
     */
    public function update(Request $request, PostTag $postTag)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_tags,slug,'.$postTag->id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $postTag->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag updated successfully!',
                'tag' => $postTag,
            ]);
        }

        return redirect()->route('admin.post-tags.index')->with('success', 'Tag updated successfully!');
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(PostTag $postTag, Request $request)
    {
        // Detach from all posts first
        $postTag->posts()->detach();
        $postTag->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Tag deleted successfully!',
            ]);
        }

        return redirect()->route('admin.post-tags.index')->with('success', 'Tag deleted successfully!');
    }
}
