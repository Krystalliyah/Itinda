<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Supplier;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'contact_email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'metadata' => [
                'address' => $this->faker->address(),
                'rating' => $this->faker->randomFloat(1, 3, 5),
            ],
        ];
    }
}