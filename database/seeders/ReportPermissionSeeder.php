<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ReportPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['title' => 'report_access', 'group' => 'Reports'],
            ['title' => 'report_create', 'group' => 'Reports'],
            ['title' => 'report_edit', 'group' => 'Reports'],
            ['title' => 'report_delete', 'group' => 'Reports'],
            ['title' => 'report_view_sales', 'group' => 'Reports'],
            ['title' => 'report_view_analytics', 'group' => 'Reports'],
            ['title' => 'report_export', 'group' => 'Reports'],
            ['title' => 'report_run', 'group' => 'Reports'],
        ];

        $createdPermissions = [];

        foreach ($permissions as $permission) {
            $createdPermission = Permission::firstOrCreate(
                ['title' => $permission['title']],
                [
                    'group' => $permission['group'],
                    'status' => true,
                ]
            );
            $createdPermissions[] = $createdPermission;
        }

        // Assign all report permissions to admin and super_admin roles
        $adminRoles = Role::whereIn('title', ['admin', 'super_admin'])->get();

        foreach ($adminRoles as $role) {
            $permissionIds = collect($createdPermissions)->pluck('id');
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }

        $this->command->info('Report permissions created: ' . count($permissions));
        $this->command->info('Permissions assigned to admin roles.');
    }
}
