<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Beverages', 'color' => '#6366f1'],
            ['name' => 'Snacks & Chips', 'color' => '#ec4899'],
            ['name' => 'Canned Goods', 'color' => '#f59e0b'],
            ['name' => 'Instant Noodles & Pasta', 'color' => '#10b981'],
            ['name' => 'Rice & Grains', 'color' => '#84cc16'],
            ['name' => 'Condiments & Sauces', 'color' => '#ef4444'],
            ['name' => 'Dairy & Eggs', 'color' => '#06b6d4'],
            ['name' => 'Bread & Bakery', 'color' => '#f97316'],
            ['name' => 'Personal Care', 'color' => '#8b5cf6'],
            ['name' => 'Household Supplies', 'color' => '#14b8a6'],
            ['name' => 'Cigarettes & Tobacco', 'color' => '#6b7280'],
            ['name' => 'Candies & Sweets', 'color' => '#d946ef'],
            ['name' => 'Frozen Foods', 'color' => '#0ea5e9'],
            ['name' => 'Fresh Produce', 'color' => '#22c55e'],
            ['name' => 'Cooking Essentials', 'color' => '#eab308'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'slug' => Str::slug($category['name']),
                    'description' => null,
                    'color' => $category['color'],
                    'parent_id' => null,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}