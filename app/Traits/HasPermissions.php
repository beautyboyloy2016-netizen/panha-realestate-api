<?php

namespace App\Traits;

use App\Http\Middleware\PermissionMiddleware;
use App\Models\User;

trait HasPermissions
{
    /**
     * Check if current user has permission
     */
    public function hasPermission(string $permission): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $middleware = new PermissionMiddleware;

        return $middleware->hasPermission(auth()->user(), $permission);
    }

    /**
     * Check if current user has all permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $middleware = new PermissionMiddleware;

        return $middleware->hasAllPermissions(auth()->user(), $permissions);
    }

    /**
     * Check if current user has any permission
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $middleware = new PermissionMiddleware;

        return $middleware->hasAnyPermission(auth()->user(), $permissions);
    }

    /**
     * Check if current user can access resource
     */
    public function canAccessResource(string $resource, string $action = 'access'): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $middleware = new PermissionMiddleware;

        return $middleware->canAccessResource(auth()->user(), $resource, $action);
    }

    /**
     * Check CRUD permissions for current user
     */
    public function canCRUD(string $resource, string $action): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $middleware = new PermissionMiddleware;

        return $middleware->canCRUD(auth()->user(), $resource, $action);
    }

    /**
     * Get current user's permissions
     */
    public function getUserPermissions(): array
    {
        if (! auth()->check()) {
            return [];
        }

        $middleware = new PermissionMiddleware;

        return $middleware->getUserPermissions(auth()->user());
    }

    /**
     * Get current user's accessible resources
     */
    public function getAccessibleResources(): array
    {
        if (! auth()->check()) {
            return [];
        }

        $middleware = new PermissionMiddleware;

        return $middleware->getAccessibleResources(auth()->user());
    }

    /**
     * Check if current user can manage users
     */
    public function canManageUsers(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $middleware = new PermissionMiddleware;

        return $middleware->canManageUsers(auth()->user());
    }

    /**
     * Check if current user can access dashboard
     */
    public function canAccessDashboard(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        $middleware = new PermissionMiddleware;

        return $middleware->canAccessDashboard(auth()->user());
    }

    /**
     * Abort if user doesn't have permission
     */
    public function authorizePermission(string $permission, string $message = 'You do not have permission to perform this action.'): void
    {
        if (! $this->hasPermission($permission)) {
            abort(403, $message);
        }
    }

    /**
     * Abort if user doesn't have any of the permissions
     */
    public function authorizeAnyPermission(array $permissions, string $message = 'You do not have permission to perform this action.'): void
    {
        if (! $this->hasAnyPermission($permissions)) {
            abort(403, $message);
        }
    }

    /**
     * Abort if user doesn't have all permissions
     */
    public function authorizeAllPermissions(array $permissions, string $message = 'You do not have permission to perform this action.'): void
    {
        if (! $this->hasAllPermissions($permissions)) {
            abort(403, $message);
        }
    }

    /**
     * Abort if user cannot access resource
     */
    public function authorizeResource(string $resource, string $action = 'access', ?string $message = null): void
    {
        if (! $this->canAccessResource($resource, $action)) {
            $message = $message ?: "You do not have permission to {$action} {$resource}.";
            abort(403, $message);
        }
    }
}
