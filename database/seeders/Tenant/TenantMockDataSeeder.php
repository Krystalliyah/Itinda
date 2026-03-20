<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

class TenantMockDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SupplierSeeder::class,
            CategorySeeder::class,
            // ProductSeeder::class, // Commented out - create products manually via vendor panel
            ProductSupplierSeeder::class,
            InventorySeeder::class,
            // OrderSeeder::class, // Commented out - create orders via customer purchases
        ]);
    }
}