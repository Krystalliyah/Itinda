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
            // Food & Drinks
            ['name' => 'Beverages', 'color' => '#6366f1'],
            ['name' => 'Snacks & Chips', 'color' => '#ec4899'],
            ['name' => 'Canned Goods', 'color' => '#f59e0b'],
            ['name' => 'Instant Noodles & Pasta', 'color' => '#10b981'],
            ['name' => 'Rice & Grains', 'color' => '#84cc16'],
            ['name' => 'Condiments & Sauces', 'color' => '#ef4444'],
            ['name' => 'Dairy & Eggs', 'color' => '#06b6d4'],
            ['name' => 'Bread & Bakery', 'color' => '#f97316'],
            ['name' => 'Candies & Sweets', 'color' => '#d946ef'],
            ['name' => 'Frozen Foods', 'color' => '#0ea5e9'],
            ['name' => 'Fresh Produce', 'color' => '#22c55e'],
            ['name' => 'Cooking Essentials', 'color' => '#eab308'],
            ['name' => 'Coffee & Tea', 'color' => '#92400e'],
            ['name' => 'Juices & Softdrinks', 'color' => '#f43f5e'],
            ['name' => 'Alcoholic Beverages', 'color' => '#7c3aed'],
            ['name' => 'Meat & Seafood', 'color' => '#dc2626'],
            ['name' => 'Spices & Seasonings', 'color' => '#d97706'],
            ['name' => 'Breakfast & Cereals', 'color' => '#ca8a04'],
            ['name' => 'Biscuits & Crackers', 'color' => '#a16207'],
            ['name' => 'Spreads & Jams', 'color' => '#b45309'],

            // Non-Food
            ['name' => 'Personal Care', 'color' => '#8b5cf6'],
            ['name' => 'Household Supplies', 'color' => '#14b8a6'],
            ['name' => 'Cigarettes & Tobacco', 'color' => '#6b7280'],
            ['name' => 'Laundry & Cleaning', 'color' => '#0284c7'],
            ['name' => 'Baby & Kids', 'color' => '#fb7185'],
            ['name' => 'Health & Wellness', 'color' => '#16a34a'],
            ['name' => 'School & Office Supplies', 'color' => '#2563eb'],
            ['name' => 'Load & Bills Payment', 'color' => '#059669'],
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