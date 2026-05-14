<?php

namespace App\Http\Controllers\Backend;

use App\Models\Permission;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PermissionController extends BaseController
{
    protected string $resource = 'permission';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['groups'] = Permission::select('group')->distinct()->whereNotNull('group')->pluck('group');

        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        return view('admin.permissions.index', $data);
    }

    /**
     * Get data for DataTables Ajax using Yajra DataTables
     */
    private function getDataTableData(Request $request)
    {
        $query = Permission::query();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('group', function ($permission) {
                return $permission->group
                    ? '<span class="badge bg-secondary">'.$permission->group.'</span>'
                    : '<span class="text-muted">No Group</span>';
            })
            ->addColumn('title', function ($permission) {
                return '<strong>'.$permission->title.'</strong>';
            })
            ->addColumn('status', function ($permission) {
                return $permission->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($permission) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-permission" data-id="'.$permission->id.'">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-permission" data-id="'.$permission->id.'">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-permission" data-id="'.$permission->id.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->editColumn('created_at', function ($permission) {
                return $permission->created_at ? $permission->created_at->format('Y-m-d H:i') : '-';
            })
            ->filterColumn('group', function ($query, $keyword) {
                $query->where('group', 'like', "%{$keyword}%");
            })
            ->filterColumn('status', function ($query, $keyword) {
                if ($keyword === 'Active') {
                    $query->where('status', 1);
                } elseif ($keyword === 'Inactive') {
                    $query->where('status', 0);
                }
            })
            ->orderColumn('group', 'group $1')
            ->rawColumns(['group', 'title', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.permissions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:permissions,title',
            'group' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $data = $request->only(['title', 'group', 'status']);
        $data['status'] = $request->has('status') ? 1 : 0;

        $permission = Permission::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🎉 Permission created successfully!',
                'title' => 'Success',
                'type' => 'success',
                'permission' => $permission,
            ]);
        }

        sweetalert()->success('Permission created successfully!');

        return redirect()->route('admin.permissions.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Permission $permission, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'permission' => $permission,
            ]);
        }

        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Permission $permission, Request $request)
    {
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'permission' => $permission,
            ]);
        }

        return view('admin.permissions.edit', compact('permission'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:permissions,title,'.$permission->id,
            'group' => 'nullable|string|max:255',
            'status' => 'boolean',
        ]);

        $data = $request->only(['title', 'group', 'status']);
        $data['status'] = $request->has('status') ? 1 : 0;

        $permission->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Permission updated successfully!',
                'title' => 'Updated',
                'type' => 'success',
                'permission' => $permission,
            ]);
        }

        sweetalert()->success('Permission updated successfully!');

        return redirect()->route('admin.permissions.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission, Request $request)
    {
        $permission->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ Permission deleted successfully!',
                'title' => 'Deleted',
                'type' => 'success',
            ]);
        }

        sweetalert()->success('Permission deleted successfully!');

        return redirect()->route('admin.permissions.index');
    }

    /**
     * Search permissions
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $permissions = Permission::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('group', 'like', "%{$query}%");
        })->paginate(15);

        return view('admin.permissions.index', compact('permissions', 'query'));
    }

    /**
     * Get permissions by group
     */
    public function byGroup(Request $request)
    {
        $group = $request->get('group', 'all');

        $query = Permission::query();

        if ($group !== 'all' && ! empty($group)) {
            $query->where('group', $group);
        }

        $permissions = $query->orderBy('group', 'asc')->orderBy('title', 'asc')->paginate(15);

        return view('admin.permissions.index', compact('permissions', 'group'));
    }
}
