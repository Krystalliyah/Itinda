<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TenantRolesSeeder extends Seeder
{
    /**
     * Run the tenant roles seeder.
     */
    public function run(): void
    {
        // Clear cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Ensure both roles exist in this tenant database
        foreach (['vendor', 'staff'] as $roleName) {
            if (! Role::where('name', $roleName)->where('guard_name', 'web')->exists()) {
                Role::create(['name' => $roleName, 'guard_name' => 'web']);
            }
        }

        // Clear cache again after creating roles
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
