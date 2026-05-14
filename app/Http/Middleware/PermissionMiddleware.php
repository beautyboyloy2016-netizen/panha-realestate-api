<?php

namespace App\Http\Middleware;

use App\Models\Permission;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to access this resource.');
        }

        $user = Auth::user();

        // Check if user has the required permission
        if (! $this->hasPermission($user, $permission)) {
            // If it's an AJAX request, return JSON response
            if ($request->ajax()) {
                return response()->json([
                    'error' => 'You do not have permission to perform this action.',
                    'permission_required' => $permission,
                ], 403);
            }

            // For regular requests, redirect back or to dashboard with error
            return redirect()->back()->with('error', 'You do not have permission to access this resource.');
        }

        return $next($request);
    }

    /**
     * Check if user has the required permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        // Super admin check - if user has dashboard_access and user_management_access, grant all permissions
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Check direct user permissions
        if ($this->hasDirectPermission($user, $permission)) {
            return true;
        }

        // Check permissions through roles
        if ($this->hasRolePermission($user, $permission)) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(User $user): bool
    {
        // Check if user has both dashboard_access and user_management_access permissions
        $hasDashboardAccess = $this->hasDirectPermission($user, 'dashboard_access') ||
          $this->hasRolePermission($user, 'dashboard_access');

        $hasUserManagement = $this->hasDirectPermission($user, 'user_management_access') ||
          $this->hasRolePermission($user, 'user_management_access');

        return $hasDashboardAccess && $hasUserManagement;
    }

    /**
     * Check if user has direct permission
     */
    private function hasDirectPermission(User $user, string $permission): bool
    {
        return $user->permissions()
            ->where('title', $permission)
            ->where('status', true)
            ->exists();
    }

    /**
     * Check if user has permission through roles
     */
    private function hasRolePermission(User $user, string $permission): bool
    {
        return $user->roles()
            ->where('status', true)
            ->whereHas('permissions', function ($query) use ($permission) {
                $query->where('title', $permission)
                    ->where('status', true);
            })
            ->exists();
    }

    /**
     * Check multiple permissions (user must have ALL permissions)
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (! $this->hasPermission($user, $permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check multiple permissions (user must have ANY permission)
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all user permissions
     */
    public function getUserPermissions(User $user): array
    {
        // Get direct permissions
        $directPermissions = $user->permissions()
            ->where('status', true)
            ->pluck('title')
            ->toArray();

        // Get role permissions
        $rolePermissions = Permission::whereHas('roles', function ($query) use ($user) {
            $query->whereHas('users', function ($subQuery) use ($user) {
                $subQuery->where('users.id', $user->id);
            })->where('status', true);
        })->where('status', true)
            ->pluck('title')
            ->toArray();

        // Merge and remove duplicates
        return array_unique(array_merge($directPermissions, $rolePermissions));
    }

    /**
     * Check permission for resource access
     */
    public function canAccessResource(User $user, string $resource, string $action = 'access'): bool
    {
        $permission = $resource.'_'.$action;

        return $this->hasPermission($user, $permission);
    }

    /**
     * Check CRUD permissions for a resource
     */
    public function canCRUD(User $user, string $resource, string $action): bool
    {
        $validActions = ['create', 'edit', 'show', 'delete', 'access'];

        if (! in_array($action, $validActions)) {
            return false;
        }

        return $this->canAccessResource($user, $resource, $action);
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(User $user): bool
    {
        return $this->hasPermission($user, 'user_management_access');
    }

    /**
     * Check if user can access admin dashboard
     */
    public function canAccessDashboard(User $user): bool
    {
        return $this->hasPermission($user, 'dashboard_access');
    }

    /**
     * Get user's accessible resources based on permissions
     */
    public function getAccessibleResources(User $user): array
    {
        $permissions = $this->getUserPermissions($user);
        $resources = [];

        foreach ($permissions as $permission) {
            // Extract resource name from permission (e.g., 'user_create' -> 'user')
            if (strpos($permission, '_') !== false) {
                $parts = explode('_', $permission);
                $resource = $parts[0];
                $action = implode('_', array_slice($parts, 1));

                if (! isset($resources[$resource])) {
                    $resources[$resource] = [];
                }
                $resources[$resource][] = $action;
            }
        }

        return $resources;
    }
}
