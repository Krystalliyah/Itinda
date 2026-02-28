<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Clear cached roles & permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Create roles
        Role::findOrCreate('admin');
        Role::findOrCreate('vendor');
        Role::findOrCreate('customer');
    }
}
