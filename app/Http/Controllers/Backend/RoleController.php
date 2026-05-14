<?php

namespace App\Http\Controllers\Backend;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class RoleController extends BaseController
{
    protected string $resource = 'role';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['permissions'] = Permission::all()->groupBy('group');

        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        return view('admin.roles.index', $data);
    }

    /**
     * Get data for DataTables Ajax using Yajra DataTables
     */
    private function getDataTableData(Request $request)
    {
        $query = Role::with('permissions');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('title', function ($role) {
                return '<strong>'.$role->title.'</strong>';
            })
            ->addColumn('permissions_count', function ($role) {
                return $role->permissions->count().' permissions';
            })
            ->addColumn('permissions', function ($role) {
                $permissions = $role->permissions->take(5)->map(function ($permission) {
                    return '<span class="badge bg-info">'.$permission->title.'</span>';
                })->join(' ');

                $moreCount = $role->permissions->count() - 5;
                if ($moreCount > 0) {
                    $permissions .= ' <span class="badge bg-secondary">+'.$moreCount.' more</span>';
                }

                return $permissions ?: '<span class="text-muted">No Permissions</span>';
            })
            ->addColumn('status', function ($role) {
                return $role->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($role) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-role" data-id="'.$role->id.'">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-role" data-id="'.$role->id.'">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-role" data-id="'.$role->id.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->editColumn('created_at', function ($role) {
                return $role->created_at ? $role->created_at->format('Y-m-d H:i') : '-';
            })
            ->filterColumn('status', function ($query, $keyword) {
                if ($keyword === 'Active') {
                    $query->where('status', 1);
                } elseif ($keyword === 'Inactive') {
                    $query->where('status', 0);
                }
            })
            ->orderColumn('permissions_count', function ($query, $order) {
                $query->withCount('permissions')->orderBy('permissions_count', $order);
            })
            ->rawColumns(['title', 'permissions', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:roles,title',
            'status' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $data = $request->only(['title', 'status']);
        $data['status'] = $request->has('status') ? 1 : 0;

        $role = Role::create($data);

        // Assign permissions if provided
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🎉 Role created successfully!',
                'title' => 'Success',
                'type' => 'success',
                'role' => $role->load('permissions'),
            ]);
        }

        sweetalert()->success('Role created successfully!');

        return redirect()->route('admin.roles.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role, Request $request)
    {
        $role->load('permissions');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'role' => $role,
            ]);
        }

        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role, Request $request)
    {
        $role->load('permissions');

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'role' => $role,
            ]);
        }

        return view('admin.roles.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'title' => 'required|string|max:255|unique:roles,title,'.$role->id,
            'status' => 'boolean',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $data = $request->only(['title', 'status']);
        $data['status'] = $request->has('status') ? 1 : 0;

        $role->update($data);

        // Update permissions if provided
        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        } else {
            // Clear all permissions if none provided
            $role->permissions()->sync([]);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ Role updated successfully!',
                'title' => 'Updated',
                'type' => 'success',
                'role' => $role->load('permissions'),
            ]);
        }

        sweetalert()->success('Role updated successfully!');

        return redirect()->route('admin.roles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role, Request $request)
    {
        // Check if role is being used by any users
        if ($role->users()->count() > 0) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role that is assigned to users!',
                    'title' => 'Error',
                    'type' => 'error',
                ], 422);
            }

            sweetalert()->error('Cannot delete role that is assigned to users!');

            return redirect()->route('admin.roles.index');
        }

        $role->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ Role deleted successfully!',
                'title' => 'Deleted',
                'type' => 'success',
            ]);
        }

        sweetalert()->success('Role deleted successfully!');

        return redirect()->route('admin.roles.index');
    }

    /**
     * Search roles
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $roles = Role::where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%");
        })->paginate(15);

        return view('admin.roles.index', compact('roles', 'query'));
    }

    /**
     * Get roles by status
     */
    public function byStatus(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = Role::query();

        switch ($status) {
            case 'active':
                $query->where('status', 1);
                break;
            case 'inactive':
                $query->where('status', 0);
                break;
            case 'with_permissions':
                $query->has('permissions');
                break;
            case 'without_permissions':
                $query->doesntHave('permissions');
                break;
        }

        $roles = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.roles.index', compact('roles', 'status'));
    }
}
