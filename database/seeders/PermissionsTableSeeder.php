<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // kyc
            'view_any_kyc_verification', 'view_kyc_verification', 'create_kyc_verification', 'update_kyc_verification', 'delete_kyc_verification', 'restore_kyc_verification', 'force_delete_kyc_verification',

            // onboarding
            'view_any_onboarding_submission', 'view_onboarding_submission', 'create_onboarding_submission', 'update_onboarding_submission', 'delete_onboarding_submission', 'restore_onboarding_submission', 'force_delete_onboarding_submission',

            // organisations
            'view_any_organisation', 'view_organisation', 'create_organisation', 'update_organisation', 'delete_organisation', 'restore_organisation', 'force_delete_organisation',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }
}
