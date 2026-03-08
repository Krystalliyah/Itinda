<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Tenant;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Beverages',
            'Snacks & Chips',
            'Canned Goods',
            'Instant Noodles & Pasta',
            'Rice & Grains',
            'Condiments & Sauces',
            'Dairy & Eggs',
            'Bread & Bakery',
            'Personal Care',
            'Household Supplies',
            'Cigarettes & Tobacco',
            'Candies & Sweets',
            'Frozen Foods',
            'Fresh Produce',
            'Cooking Essentials',
        ];

        Tenant::all()->each(function ($tenant) use ($categories) {
            tenancy()->initialize($tenant);

            foreach ($categories as $name) {
                DB::table('categories')->updateOrInsert(
                    ['name' => $name],
                    [
                        'slug' => Str::slug($name),
                        'description' => null,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }

            tenancy()->end();
        });
    }
}