<?php

namespace Nucleus\Permit\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermitSeeder extends Seeder
{
    /**
     * Seed initial permissions and assign them to roles.
     *
     * Naming convention: <action>_<resource>, e.g. view_users, manage_settings.
     * Forks should extend this list with their domain-specific permissions.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User management
            'view_users',
            'create_user',
            'edit_user',
            'delete_user',
            'impersonate_user',

            // Role & permission management
            'view_roles',
            'create_role',
            'edit_role',
            'delete_role',
            'view_permissions',
            'assign_permissions',

            // KYC
            'view_kyc',
            'approve_kyc',
            'reject_kyc',

            // Onboarding
            'view_onboarding',
            'manage_onboarding',

            // Organisation
            'view_organisations',
            'create_organisation',
            'edit_organisation',
            'delete_organisation',

            // Reports & analytics
            'view_reports',
            'export_reports',

            // Admin panel access
            'access_admin_panel',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->syncPermissions(Permission::where('guard_name', 'web')->get());
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
