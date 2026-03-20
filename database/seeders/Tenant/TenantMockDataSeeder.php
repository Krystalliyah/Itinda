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
            ProductSeeder::class,
            ProductSupplierSeeder::class,
            InventorySeeder::class,
            OrderSeeder::class,
        ]);
    }
}