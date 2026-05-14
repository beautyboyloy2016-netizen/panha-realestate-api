<?php

namespace App\Http\Controllers\Backend;

use App\Models\Role;
use App\Models\User;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;
use Yajra\DataTables\Facades\DataTables;

class UserController extends BaseController
{
    use ImageUploadTrait;

    protected string $resource = 'user';

    protected array $additionalPermissions = ['user_management_access'];

    public function __construct()
    {
        parent::__construct();

        // Apply specific permissions for user management methods
        $this->applyMethodPermission('user_profile_password_edit', ['changePassword']);
        $this->applyMethodPermission('user_edit', ['verifyEmail', 'unverifyEmail', 'toggleVerification']);
        $this->applyMethodPermission('user_show', ['updateLastLogin']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data['roles'] = Role::all();
        if ($request->ajax()) {
            return $this->getDataTableData($request);
        }

        return view('admin.users.index', $data);
    }

    /**
     * Get data for DataTables Ajax using Yajra DataTables
     */
    private function getDataTableData(Request $request)
    {
        $query = User::with(['roles']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('avatar', function ($user) {
                $imageUrl = $user->avatar_url ?? asset('images/default-avatar.png');
                return '<img src="'.$imageUrl.'" alt="Avatar" class="img-thumbnail" style="width:50px; height:50px; object-fit:cover;">';
            })
            ->addColumn('name', function ($user) {
                return '<strong>'.$user->full_name.'</strong><br><small class="text-muted">'.($user->username ?? 'N/A').'</small>';
            })
            ->addColumn('phone', function ($user) {
                return $user->phone_no ?? 'N/A';
            })
            ->addColumn('roles', function ($user) {
                $roles = $user->roles->map(function ($role) {
                    return '<span class="badge bg-info">'.$role->title.'</span>';
                })->join(' ');
                return $roles ?: '<span class="text-muted">No Role</span>';
            })
            ->addColumn('status', function ($user) {
                return $user->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('actions', function ($user) {
                return '
                    <div class="btn-group">
                        <button class="btn btn-sm btn-info view-user" data-id="'.$user->id.'">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-warning edit-user" data-id="'.$user->id.'">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger delete-user" data-id="'.$user->id.'">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>';
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at ? $user->created_at->format('Y-m-d H:i') : '-';
            })
            ->filterColumn('name', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('first_name', 'like', "%{$keyword}%")
                        ->orWhere('last_name', 'like', "%{$keyword}%")
                        ->orWhere('username', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('roles', function ($query, $keyword) {
                $query->whereHas('roles', function ($q) use ($keyword) {
                    $q->where('title', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                if ($keyword === 'Active') {
                    $query->where('status', 1);
                } elseif ($keyword === 'Inactive') {
                    $query->where('status', 0);
                }
            })
            ->orderColumn('name', 'first_name $1')
            ->rawColumns(['avatar', 'name', 'roles', 'status', 'actions'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone_no' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'avatar_url' => 'nullable|url',
            'status' => 'boolean',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $data = $request->except(['avatar', 'avatar_url', 'old_avatar', 'password_confirmation', 'roles']);
        // Handle avatar upload - Check both file upload and URL from media selector
        if ($request->hasFile('avatar')) {
            // Direct file upload
            $data['avatar'] = $this->uploadImage($request, 'avatar', 'uploads/avatars', 'avatar_');
            Log::info('Avatar uploaded from file: '.$data['avatar']);
        } elseif ($request->filled('avatar_url')) {
            // Media selector URL - convert full URL to relative path
            $avatarUrl = $request->avatar_url;
            if (str_contains($avatarUrl, '/storage/')) {
                // Extract relative path from full URL
                $data['avatar'] = str_replace(url('/storage/'), '', $avatarUrl);
            } else {
                // Keep external URLs as-is
                $data['avatar'] = $avatarUrl;
            }
            Log::info('Avatar set from URL: '.$data['avatar']);
        }

        // Create the user
        $user = User::create($data);

        // Assign roles if provided
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🎉 User created successfully!',
                'title' => 'Success',
                'type' => 'success',
                'user' => $user->load('roles'),
            ]);
        }

        sweetalert()->success('User created successfully!');

        return redirect()->route('admin.users.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user, Request $request)
    {
        $user->load(['roles', 'vendor']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        }

        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user, Request $request)
    {
        $user->load(['roles']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone_no' => 'nullable|string|max:20',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'avatar_url' => 'nullable|string',
            'status' => 'boolean',
            'email_verified_at' => 'nullable|date',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $data = $request->except(['avatar', 'avatar_url', 'old_avatar', 'password_confirmation', 'roles']);

        // Handle avatar from media library only
        if ($request->filled('avatar_url')) {
            // Media selector URL - convert full URL to relative path
            $avatarUrl = $request->avatar_url;
            $relativePath = $avatarUrl;

            // Remove the domain and /storage/ prefix to get just /media/images/...
            if (str_contains($avatarUrl, '/storage/media/')) {
                // Extract path after /storage/
                $relativePath = substr($avatarUrl, strpos($avatarUrl, '/storage/') + strlen('/storage/'));
            } elseif (str_contains($avatarUrl, '/media/')) {
                // Already in correct format /media/images/...
                $relativePath = substr($avatarUrl, strpos($avatarUrl, 'media/'));
            }

            // Update avatar path (no file upload, just reference)
            $data['avatar'] = $relativePath;
            Log::info('Avatar updated from media library: '.$data['avatar']);
        }

        // Remove password from data if empty (so it doesn't get updated)
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        // Update roles if provided
        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '✅ User updated successfully!',
                'title' => 'Updated',
                'type' => 'success',
                'user' => $user->load('roles'),
            ]);
        }

        sweetalert()->success('User updated successfully!');

        return redirect()->route('admin.users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Request $request)
    {
        // Delete avatar if exists - Fixed: correct field name
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => '🗑️ User deleted successfully!',
                'title' => 'Deleted',
                'type' => 'success',
            ]);
        }

        sweetalert()->success('User deleted successfully!');

        return redirect()->route('admin.users.index');
    }

    /**
     * Verify user's email
     */
    public function verifyEmail(User $user)
    {
        $user->update([
            'email_verified_at' => now(),
            'is_verified' => true,
        ]);

        return redirect()->back()
            ->with('success', 'User email verified successfully.');
    }

    /**
     * Unverify user's email
     */
    public function unverifyEmail(User $user)
    {
        $user->update([
            'email_verified_at' => null,
            'is_verified' => false,
        ]);

        return redirect()->back()
            ->with('success', 'User email unverified successfully.');
    }

    /**
     * Toggle user verification status
     */
    public function toggleVerification(User $user)
    {
        $user->update([
            'is_verified' => ! $user->is_verified,
            'email_verified_at' => ! $user->is_verified ? now() : null,
        ]);

        $status = $user->is_verified ? 'verified' : 'unverified';

        return redirect()->back()
            ->with('success', "User has been {$status} successfully.");
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(User $user)
    {
        $user->update(['last_login' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Get user profile
     */
    public function profile()
    {
        $user = auth()->user();

        return view('users.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,'.$user->id,
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'phone_no' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('avatar');

        // Handle avatar upload - Fixed: correct field name and use trait method
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $this->uploadImage($request, 'avatar', 'uploads/avatars', 'avatar_');
        }

        $user->update($data);

        return redirect()->route('users.profile')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()
            ->with('success', 'Password changed successfully.');
    }

    /**
     * Search users
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        $users = User::where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
                ->orWhere('last_name', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('phone_no', 'like', "%{$query}%");
        })->paginate(15);

        return view('admin.users.index', compact('users', 'query'));
    }

    /**
     * Get users by status
     */
    public function byStatus(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = User::query();

        switch ($status) {
            case 'verified':
                $query->where('is_verified', true);
                break;
            case 'unverified':
                $query->where('is_verified', false);
                break;
            case 'with_avatar':
                $query->whereNotNull('avatar'); // Fixed: correct field name
                break;
            case 'without_avatar':
                $query->whereNull('avatar'); // Fixed: correct field name
                break;
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users', 'status'));
    }
}
