<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::all();

        foreach ($categories as $category) {

            Product::factory()
                ->count(5)
                ->create([
                    'category_id' => $category->id
                ]);

        }
    }
}