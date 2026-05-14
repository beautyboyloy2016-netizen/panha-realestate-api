<?php

namespace App\Http\Controllers\Backend;

use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PostCategoryController extends BaseController
{
    protected string $resource = 'post_category';

    /**
     * Display a listing of categories.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        $parentCategories = PostCategory::active()->parentOnly()->ordered()->get();

        return view('admin.post-categories.index', compact('parentCategories'));
    }

    /**
     * Get data for DataTables Ajax
     */
    private function getDataTableData(Request $request)
    {
        $query = PostCategory::with(['parent', 'posts'])->withCount('posts');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('name', function ($category) {
                $icon = $category->icon ? '<i class="fas '.$category->icon.' me-2"></i>' : '';
                $color = $category->color ? 'style="color: '.$category->color.'"' : '';
                $parent = $category->parent ? '<small class="text-muted d-block">Parent: '.$category->parent->name.'</small>' : '';
                return '<span '.$color.'>'.$icon.'<strong>'.$category->name.'</strong></span>'.$parent;
            })
            ->addColumn('slug', function ($category) {
                return '<code>'.$category->slug.'</code>';
            })
            ->addColumn('posts_count', function ($category) {
                return '<span class="badge bg-info">'.$category->posts_count.' posts</span>';
            })
            ->addColumn('status', function ($category) {
                return $category->is_active
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($category) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-warning edit-category" data-id="'.$category->id.'" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-category" data-id="'.$category->id.'" title="Delete">
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
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_categories,slug',
            'parent_id' => 'nullable|exists:post_categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        $category = PostCategory::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully!',
                'category' => $category,
            ]);
        }

        return redirect()->route('admin.post-categories.index')->with('success', 'Category created successfully!');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(PostCategory $postCategory, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'category' => $postCategory,
            ]);
        }

        $parentCategories = PostCategory::active()->parentOnly()->where('id', '!=', $postCategory->id)->ordered()->get();

        return view('admin.post-categories.edit', compact('postCategory', 'parentCategories'));
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, PostCategory $postCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:post_categories,slug,'.$postCategory->id,
            'parent_id' => 'nullable|exists:post_categories,id',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        // Prevent category from being its own parent
        if ($validated['parent_id'] == $postCategory->id) {
            $validated['parent_id'] = null;
        }

        $postCategory->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully!',
                'category' => $postCategory,
            ]);
        }

        return redirect()->route('admin.post-categories.index')->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category.
     */
    public function destroy(PostCategory $postCategory, Request $request)
    {
        // Check if category has posts
        if ($postCategory->posts()->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete category with associated posts!',
                ], 422);
            }
            return redirect()->route('admin.post-categories.index')->with('error', 'Cannot delete category with associated posts!');
        }

        // Move child categories to root
        PostCategory::where('parent_id', $postCategory->id)->update(['parent_id' => null]);

        $postCategory->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully!',
            ]);
        }

        return redirect()->route('admin.post-categories.index')->with('success', 'Category deleted successfully!');
    }
}
