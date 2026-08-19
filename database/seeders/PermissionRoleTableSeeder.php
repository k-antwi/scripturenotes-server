<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class PermissionRoleTableSeeder extends Seeder
{
    /**
     * Seed the role-permission associations.
     *
     * Phase 4 introduces a generic Provider role with its own permission
     * grants; until then this seeder only resets the pivot table so a fresh
     * install has no stale role-permission links.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        DB::table('role_has_permissions')->delete();
    }
}
