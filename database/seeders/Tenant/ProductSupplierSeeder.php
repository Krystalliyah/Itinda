<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class ProductSupplierSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $products = Product::all();
        $suppliers = Supplier::all();

        foreach ($products as $product) {

            $supplier = $suppliers->random();

            DB::table('product_supplier')->insert([
                'product_id' => $product->id,
                'supplier_id' => $supplier->id,
                'cost' => $faker->randomFloat(2, 5, 50),
                'supplier_sku' => strtoupper($faker->bothify('SUP-#####')),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        }
    }
}