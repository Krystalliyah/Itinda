<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

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

        foreach ($categories as $name) {
            Category::create([
                'name' => ucfirst($name),
                'slug' => Str::slug($name),
                'description' => $faker->sentence(),
            ]);
        }
    }
}