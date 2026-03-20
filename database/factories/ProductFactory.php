<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'sku' => 'SKU-' . strtoupper($this->faker->unique()->bothify('??###??')),
            'name' => ucfirst($this->faker->words(3, true)),
            'description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 500),
            'stock' => 0,
            'image_path' => null,
            'is_active' => $this->faker->boolean(80), // 80% true, 20% false
        ];
    }
}