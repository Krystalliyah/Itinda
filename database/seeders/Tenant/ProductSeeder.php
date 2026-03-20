<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = collect();

        tenancy()->central(function () use (&$categories) {
            $categories = Category::select('id', 'name')->get();
        });

        $productMap = [
            'Beverages' => [
                'Coca-Cola 350ml',
                'Pepsi 350ml',
                'Sprite 350ml',
                'Royal Tru-Orange 350ml',
                'Summit Drinking Water 500ml',
            ],
            'Snacks & Chips' => [
                'Piattos Cheese',
                'Nova Country Cheddar',
                'V-Cut Barbecue',
                'Clover Chips',
                'Boy Bawang Garlic',
            ],
            'Canned Goods' => [
                'Argentina Corned Beef',
                'Mega Sardines',
                '555 Tuna Flakes',
                'Century Tuna Hot & Spicy',
                'San Marino Corned Tuna',
            ],
            'Instant Noodles & Pasta' => [
                'Lucky Me Pancit Canton',
                'Lucky Me Beef Noodles',
                'Payless Xtra Big',
                'UFC Spaghetti',
                'El Real Pasta',
            ],
            'Rice & Grains' => [
                'Jasmine Rice 1kg',
                'Sinandomeng Rice 1kg',
                'Brown Rice 1kg',
                'Malagkit Rice 1kg',
                'Corn Grits 1kg',
            ],
            'Condiments & Sauces' => [
                'Silver Swan Soy Sauce',
                'Datu Puti Vinegar',
                'UFC Banana Ketchup',
                'Mang Tomas All Around Sarsa',
                'Del Monte Tomato Sauce',
            ],
            'Dairy & Eggs' => [
                'Bear Brand Powdered Milk',
                'Alaska Evaporada',
                'Angel Condensada',
                'Fresh Milk 1L',
                'Chicken Eggs Dozen',
            ],
            'Bread & Bakery' => [
                'Loaf Bread',
                'Pandesal Pack',
                'Ensaymada',
                'Monay',
                'Chocolate Cake Slice',
            ],
            'Personal Care' => [
                'Safeguard Soap',
                'Colgate Toothpaste',
                'Palmolive Shampoo',
                'Nivea Deodorant',
                'Toothbrush Medium',
            ],
            'Household Supplies' => [
                'Joy Dishwashing Liquid',
                'Surf Powder Detergent',
                'Zonrox Bleach',
                'Champion Fabric Conditioner',
                'Trash Bag Roll',
            ],
            'Cigarettes & Tobacco' => [
                'Marlboro Red',
                'Winston Blue',
                'Hope Menthol',
                'Fortune Tribal',
                'Camel Yellow',
            ],
            'Candies & Sweets' => [
                'Maxx Candy',
                'Cloud 9 Bar',
                'Chocnut',
                'Mentos Mint',
                'White Rabbit Candy',
            ],
            'Frozen Foods' => [
                'Frozen Hotdog 1kg',
                'Frozen Tocino 500g',
                'Frozen Longganisa 500g',
                'Chicken Nuggets 500g',
                'Fish Ball 500g',
            ],
            'Fresh Produce' => [
                'Banana Lakatan',
                'Apple Fuji',
                'Tomato 1kg',
                'Onion Red 1kg',
                'Potato 1kg',
            ],
            'Cooking Essentials' => [
                'Golden Fiesta Oil 1L',
                'Iodized Salt 500g',
                'White Sugar 1kg',
                'Garlic 250g',
                'Black Pepper 100g',
            ],
        ];

        foreach ($categories as $category) {
            $products = $productMap[$category->name] ?? [
                "{$category->name} Item 1",
                "{$category->name} Item 2",
                "{$category->name} Item 3",
                "{$category->name} Item 4",
                "{$category->name} Item 5",
            ];

            foreach ($products as $index => $productName) {
                Product::create([
                    'sku' => strtoupper('SKU-' . tenant('id') . '-' . $category->id . '-' . ($index + 1)),
                    'name' => $productName,
                    'description' => 'Sample product for ' . $category->name,
                    'category_id' => $category->id,
                    'price' => fake()->randomFloat(2, 10, 500),
                    'stock' => 0,
                    'image_path' => null,
                    'is_active' => $index !== 4,
                ]);
            }
        }
    }
}