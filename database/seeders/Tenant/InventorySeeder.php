<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\InventoryMovement;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();

        foreach ($products as $product) {

            $movement = InventoryMovement::factory()->create([
                'product_id' => $product->id
            ]);

            $quantity = $movement->quantity;

            $product->increment('stock', $quantity);

        }
    }
}