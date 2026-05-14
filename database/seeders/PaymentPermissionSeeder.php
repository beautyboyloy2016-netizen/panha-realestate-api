<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PaymentPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionsData = [
            // Payment Method Management
            'payment_methods' => [
                'payment_method_access' => 'Access Payment Methods',
                'payment_method_create' => 'Create Payment Methods',
                'payment_method_edit' => 'Edit Payment Methods',
                'payment_method_delete' => 'Delete Payment Methods',
            ],

            // Invoice Management
            'invoices' => [
                'invoice_access' => 'Access Invoices',
                'invoice_create' => 'Create Invoices',
                'invoice_edit' => 'Edit Invoices',
                'invoice_delete' => 'Delete Invoices',
                'invoice_send' => 'Send Invoices',
            ],

            // Transaction Management
            'transactions' => [
                'transaction_access' => 'Access Transactions',
                'transaction_create' => 'Create Transactions',
                'transaction_edit' => 'Edit Transactions',
                'transaction_delete' => 'Delete Transactions',
                'transaction_approve' => 'Approve Transactions',
                'transaction_refund' => 'Refund Transactions',
            ],
        ];

        $createdPermissions = [];

        foreach ($permissionsData as $group => $groupPermissions) {
            foreach ($groupPermissions as $key => $title) {
                // Check if permission already exists
                $existing = Permission::where('title', $key)->first();
                if (!$existing) {
                    $permission = Permission::create([
                        'group' => $group,
                        'title' => $key,
                        'status' => true,
                    ]);
                    $createdPermissions[] = $permission;
                    $this->command->info("Created permission: {$key}");
                } else {
                    $createdPermissions[] = $existing;
                    $this->command->warn("Permission already exists: {$key}");
                }
            }
        }

        // Assign all payment permissions to admin and super_admin roles
        $adminRoles = Role::whereIn('title', ['admin', 'super_admin'])->get();

        foreach ($adminRoles as $role) {
            $permissionIds = collect($createdPermissions)->pluck('id')->toArray();
            $role->permissions()->syncWithoutDetaching($permissionIds);
            $this->command->info("Assigned payment permissions to role: {$role->title}");
        }

        $this->command->info('Payment permissions seeding completed!');
    }
}
