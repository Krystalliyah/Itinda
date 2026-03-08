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

        // Make sure the "vendor" role exists in this tenant database
        if (! Role::where('name','vendor')->where('guard_name','web')->exists()) {
            Role::create(['name' => 'vendor', 'guard_name' => 'web']);
        }
        
        // Clear cache again after creating roles
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
