<?php

namespace App\Http\Controllers\Backend;

use App\Traits\HasPermissions;
use Illuminate\Routing\Controller;

abstract class BaseController extends Controller
{
    use HasPermissions;

    /**
     * The resource name for permission checking
     */
    protected string $resource;

    /**
     * Additional permissions required for this controller
     */
    protected array $additionalPermissions = [];

    public function __construct()
    {
        // Ensure user is authenticated
        $this->middleware('auth');

        // Ensure user has dashboard access
        $this->middleware('permission:dashboard_access');

        // Apply resource-based permissions if resource is defined
        if (isset($this->resource)) {
            $this->applyResourcePermissions();
        }

        // Apply additional permissions if defined
        if (! empty($this->additionalPermissions)) {
            foreach ($this->additionalPermissions as $permission) {
                $this->middleware("permission:{$permission}");
            }
        }
    }

    /**
     * Apply standard CRUD permissions for the resource
     */
    protected function applyResourcePermissions(): void
    {
        // Access permission for index and show methods
        $this->middleware("permission:{$this->resource}_access")
            ->only(['index', 'show']);

        // Create permission for create and store methods
        $this->middleware("permission:{$this->resource}_create")
            ->only(['create', 'store']);

        // Edit permission for edit and update methods
        $this->middleware("permission:{$this->resource}_edit")
            ->only(['edit', 'update']);

        // Delete permission for destroy method
        $this->middleware("permission:{$this->resource}_delete")
            ->only(['destroy']);
    }

    /**
     * Apply custom permission to specific methods
     */
    protected function applyMethodPermission(string $permission, array $methods): void
    {
        $this->middleware("permission:{$permission}")->only($methods);
    }

    /**
     * Apply permission to all methods except specified ones
     */
    protected function applyPermissionExcept(string $permission, array $methods): void
    {
        $this->middleware("permission:{$permission}")->except($methods);
    }
}
