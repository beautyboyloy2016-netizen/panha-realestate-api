<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use App\Models\Permission;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUserRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('permission_user')->truncate();
        DB::table('role_user')->truncate();
        DB::table('permission_role')->truncate();
        User::truncate();
        Role::truncate();
        Permission::truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create Permissions grouped by functionality
        $permissions = $this->createPermissions();

        // Create Roles
        $roles = $this->createRoles();

        // Assign Permissions to Roles
        $this->assignPermissionsToRoles($permissions, $roles);

        // Create Users
        $users = $this->createUsers();

        // Assign Roles to Users
        $this->assignRolesToUsers($users, $roles);

        // Assign direct permissions to specific users (optional)
        $this->assignDirectPermissionsToUsers($users, $permissions);

        $this->command->info('Database seeding completed successfully!');
    }

    /**
     * Create permissions grouped by functionality
     */
    private function createPermissions(): array
    {
        $permissionsData = [
            // System Access Permissions
            'system' => [
                'dashboard_access' => 'Access Dashboard',
                'user_management_access' => 'Access User Management',
            ],

            // User Management Permissions
            'users' => [
                'user_access' => 'Access Users',
                'users.view' => 'View Users',
                'user_create' => 'Create Users',
                'users.create' => 'Create Users',
                'user_edit' => 'Edit Users',
                'users.edit' => 'Edit Users',
                'user_delete' => 'Delete Users',
                'users.delete' => 'Delete Users',
                'users.export' => 'Export Users',
            ],

            // Role Management Permissions
            'roles' => [
                'role_access' => 'Access Roles',
                'roles.view' => 'View Roles',
                'role_create' => 'Create Roles',
                'roles.create' => 'Create Roles',
                'role_edit' => 'Edit Roles',
                'roles.edit' => 'Edit Roles',
                'role_delete' => 'Delete Roles',
                'roles.delete' => 'Delete Roles',
                'roles.assign' => 'Assign Roles',
            ],

            // Permission Management
            'permissions' => [
                'permission_access' => 'Access Permissions',
                'permissions.view' => 'View Permissions',
                'permission_create' => 'Create Permissions',
                'permissions.create' => 'Create Permissions',
                'permission_edit' => 'Edit Permissions',
                'permissions.edit' => 'Edit Permissions',
                'permission_delete' => 'Delete Permissions',
                'permissions.delete' => 'Delete Permissions',
                'permissions.assign' => 'Assign Permissions',
            ],

            // Content Management
            'content' => [
                'content_access' => 'Access Content',
                'content.view' => 'View Content',
                'content_create' => 'Create Content',
                'content.create' => 'Create Content',
                'content_edit' => 'Edit Content',
                'content.edit' => 'Edit Content',
                'content_delete' => 'Delete Content',
                'content.delete' => 'Delete Content',
                'content.publish' => 'Publish Content',
            ],

            // Property Management
            'properties' => [
                'property_access' => 'Access Properties',
                'property_create' => 'Create Properties',
                'property_edit' => 'Edit Properties',
                'property_delete' => 'Delete Properties',
                'property_publish' => 'Publish Properties',
            ],

            // Project Management
            'projects' => [
                'project_access' => 'Access Projects',
                'project_create' => 'Create Projects',
                'project_edit' => 'Edit Projects',
                'project_delete' => 'Delete Projects',
                'project_publish' => 'Publish Projects',
            ],

            // News Article Management
            'news_articles' => [
                'news_article_access' => 'Access News Articles',
                'news_article_create' => 'Create News Articles',
                'news_article_edit' => 'Edit News Articles',
                'news_article_delete' => 'Delete News Articles',
                'news_article_publish' => 'Publish News Articles',
            ],

            // Inquiry Management
            'inquiries' => [
                'inquiry_access' => 'Access Inquiries',
                'inquiry_view' => 'View Inquiries',
                'inquiry_reply' => 'Reply to Inquiries',
                'inquiry_delete' => 'Delete Inquiries',
                'inquiry_export' => 'Export Inquiries',
            ],

            // Reports
            'reports' => [
                'report_access' => 'Access Reports',
                'reports.view' => 'View Reports',
                'report_create' => 'Create Reports',
                'reports.create' => 'Create Reports',
                'reports.export' => 'Export Reports',
                'reports.analytics' => 'View Analytics',
            ],

            // Settings
            'settings' => [
                'setting_access' => 'Access Settings',
                'settings.view' => 'View Settings',
                'setting_edit' => 'Edit Settings',
                'settings.edit' => 'Edit Settings',
                'settings.system' => 'System Settings',
                'settings.backup' => 'Backup System',
            ],

            // Translation Management
            'translations' => [
                'translation_access' => 'Access Translations',
                'translation_create' => 'Create Translations',
                'translation_edit' => 'Edit Translations',
                'translation_delete' => 'Delete Translations',
                'translation_export' => 'Export Translations',
            ],

            // Language File Management
            'language_files' => [
                'language_file_access' => 'Access Language Files',
                'language_file_create' => 'Create Language Keys',
                'language_file_edit' => 'Edit Language Keys',
                'language_file_delete' => 'Delete Language Keys',
                'language_file_sync' => 'Sync Language Keys',
            ],

            // Blog Post Management
            'posts' => [
                'post_access' => 'Access Blog Posts',
                'post_create' => 'Create Blog Posts',
                'post_edit' => 'Edit Blog Posts',
                'post_delete' => 'Delete Blog Posts',
                'post_publish' => 'Publish Blog Posts',
            ],

            // Blog Category Management
            'post_categories' => [
                'post_category_access' => 'Access Post Categories',
                'post_category_create' => 'Create Post Categories',
                'post_category_edit' => 'Edit Post Categories',
                'post_category_delete' => 'Delete Post Categories',
            ],

            // Blog Tag Management
            'post_tags' => [
                'post_tag_access' => 'Access Post Tags',
                'post_tag_create' => 'Create Post Tags',
                'post_tag_edit' => 'Edit Post Tags',
                'post_tag_delete' => 'Delete Post Tags',
            ],

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

        $permissions = [];

        foreach ($permissionsData as $group => $groupPermissions) {
            foreach ($groupPermissions as $key => $title) {
                $permission = Permission::create([
                    'group' => $group,
                    'title' => $key,
                    'status' => true,
                ]);

                // Add translations for all languages
                if (in_array(\App\Traits\HasTranslations::class, class_uses_recursive(Permission::class))) {
                    $permission->setTranslation('title', $title, 'en');
                    $permission->setTranslation('title', $title . ' (ខ្មែរ)', 'km');
                    $permission->setTranslation('title', $title . ' (中文)', 'zh');
                    $permission->setTranslation('title', $title . ' (Français)', 'fr');
                }

                // If Permission model uses HasMetaData trait, add meta data
                if (in_array(\App\Traits\HasMetaData::class, class_uses_recursive(Permission::class))) {
                    $permission->setMeta('meta_title', $title . ' - Permission', 'en');
                    $permission->setMeta('meta_description', 'Permission for ' . $title, 'en');
                }

                $permissions[$key] = $permission;
            }
        }

        return $permissions;
    }

    /**
     * Create roles
     */
    private function createRoles(): array
    {
        $rolesData = [
            'super_admin' => [
                'title' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
            ],
            'admin' => [
                'title' => 'Administrator',
                'description' => 'Administrative access with most permissions',
            ],
            'manager' => [
                'title' => 'Manager',
                'description' => 'Management level access',
            ],
            'editor' => [
                'title' => 'Editor',
                'description' => 'Content editing access',
            ],
            'viewer' => [
                'title' => 'Viewer',
                'description' => 'Read-only access',
            ],
            'user' => [
                'title' => 'User',
                'description' => 'Basic user access',
            ],
        ];

        $roles = [];

        foreach ($rolesData as $key => $data) {
            $role = Role::create([
                'title' => $key,
                'status' => true,
            ]);

            // Add translations for all languages
            if (in_array(\App\Traits\HasTranslations::class, class_uses_recursive(Role::class))) {
                $role->setTranslation('title', $data['title'], 'en');
                $role->setTranslation('title', $data['title'] . ' (ខ្មែរ)', 'km');
                $role->setTranslation('title', $data['title'] . ' (中文)', 'zh');
                $role->setTranslation('title', $data['title'] . ' (Français)', 'fr');
            }

            // If Role model uses HasMetaData trait, add meta data
            if (in_array(\App\Traits\HasMetaData::class, class_uses_recursive(Role::class))) {
                $role->setMeta('meta_title', $data['title'] . ' Role', 'en');
                $role->setMeta('meta_description', $data['description'], 'en');
            }

            $roles[$key] = $role;
        }

        return $roles;
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(array $permissions, array $roles): void
    {
        // Super Admin - Gets all permissions
        $roles['super_admin']->permissions()->attach(
            collect($permissions)->pluck('id')->toArray()
        );

        // Admin - Gets most permissions except system critical ones
        $adminPermissions = collect($permissions)->filter(function ($permission) {
            return !in_array($permission->title, [
                'settings.system',
                'settings.backup',
                'permissions.delete',
            ]);
        })->pluck('id')->toArray();
        $roles['admin']->permissions()->attach($adminPermissions);

        // Manager - Gets user, content, and report permissions
        $managerPermissions = collect($permissions)->filter(function ($permission) {
            return in_array($permission->group, ['users', 'content', 'reports']) &&
                   !str_contains($permission->title, '.delete');
        })->pluck('id')->toArray();
        $roles['manager']->permissions()->attach($managerPermissions);

        // Editor - Gets content permissions
        $editorPermissions = collect($permissions)->filter(function ($permission) {
            return $permission->group === 'content';
        })->pluck('id')->toArray();
        $roles['editor']->permissions()->attach($editorPermissions);

        // Viewer - Gets only view permissions
        $viewerPermissions = collect($permissions)->filter(function ($permission) {
            return str_contains($permission->title, '.view');
        })->pluck('id')->toArray();
        $roles['viewer']->permissions()->attach($viewerPermissions);

        // User - Gets basic view permissions
        $userPermissions = collect($permissions)->filter(function ($permission) {
            return in_array($permission->title, [
                'content.view',
                'reports.view',
            ]);
        })->pluck('id')->toArray();
        $roles['user']->permissions()->attach($userPermissions);
    }

    /**
     * Create users
     */
    private function createUsers(): array
    {
        $users = [];

        // Create Super Admin
        $users['super_admin'] = User::create([
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'username' => 'superadmin',
            'email' => 'superadmin@login.com',
            'phone_no' => '+1234567890',
            'password' => Hash::make('SuperAdmin@123'),
            'email_verified_at' => now(),
            'is_verified' => true,
            'last_login' => now(),
            'avatar' => 'avatars/superadmin.jpg',
            'remember_token' => Str::random(10),
        ]);

        // Create Admin
        $users['admin'] = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'username' => 'admin',
            'email' => 'admin@login.com',
            'phone_no' => '+1234567891',
            'password' => Hash::make('Admin@123'),
            'email_verified_at' => now(),
            'is_verified' => true,
            'last_login' => now()->subDays(1),
            'avatar' => 'avatars/admin.jpg',
            'remember_token' => Str::random(10),
        ]);

        // Create Manager
        $users['manager'] = User::create([
            'first_name' => 'John',
            'last_name' => 'Manager',
            'username' => 'johnmanager',
            'email' => 'manager@login.com',
            'phone_no' => '+1234567892',
            'password' => Hash::make('Manager@123'),
            'email_verified_at' => now(),
            'is_verified' => true,
            'last_login' => now()->subDays(2),
            'remember_token' => Str::random(10),
        ]);

        // Create Editor
        $users['editor'] = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Editor',
            'username' => 'janeeditor',
            'email' => 'editor@login.com',
            'phone_no' => '+1234567893',
            'password' => Hash::make('Editor@123'),
            'email_verified_at' => now(),
            'is_verified' => true,
            'last_login' => now()->subDays(3),
            'remember_token' => Str::random(10),
        ]);

        // Create regular users
        for ($i = 1; $i <= 5; $i++) {
            $users['user_' . $i] = User::create([
                'first_name' => 'User',
                'last_name' => 'Number' . $i,
                'username' => 'user' . $i,
                'email' => 'user' . $i . '@login.com',
                'phone_no' => '+123456789' . $i,
                'password' => Hash::make('User@123'),
                'email_verified_at' => $i <= 3 ? now() : null,
                'is_verified' => $i <= 3,
                'last_login' => $i <= 3 ? now()->subDays($i + 3) : null,
                'remember_token' => Str::random(10),
            ]);
        }

        // If User model uses HasMetaData trait, add meta data for some users
        if (method_exists($users['super_admin'], 'setMeta')) {
            $users['super_admin']->setMeta('meta_title', 'Super Administrator Profile', 'en');
            $users['super_admin']->setMeta('meta_description', 'System Super Administrator with full access', 'en');
        }

        return $users;
    }

    /**
     * Assign roles to users
     */
    private function assignRolesToUsers(array $users, array $roles): void
    {
        $users['super_admin']->roles()->attach($roles['super_admin']->id);
        $users['admin']->roles()->attach($roles['admin']->id);
        $users['manager']->roles()->attach($roles['manager']->id);
        $users['editor']->roles()->attach($roles['editor']->id);

        // Assign user role to regular users
        for ($i = 1; $i <= 5; $i++) {
            $users['user_' . $i]->roles()->attach($roles['user']->id);
        }
    }

    /**
     * Assign direct permissions to specific users (optional)
     */
    private function assignDirectPermissionsToUsers(array $users, array $permissions): void
    {
        // Give editor user direct permission to delete content (beyond their role)
        if (isset($permissions['content.delete'])) {
            $users['editor']->permissions()->attach(
                $permissions['content.delete']->id,
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Give a regular user some additional direct permissions
        if (isset($users['user_1']) && isset($permissions['content.create'])) {
            $users['user_1']->permissions()->attach(
                $permissions['content.create']->id,
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }
}
