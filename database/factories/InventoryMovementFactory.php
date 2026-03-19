<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\InventoryMovement;

class InventoryMovementFactory extends Factory
{
    protected $model = InventoryMovement::class;

    public function definition(): array
    {
        return [
            'quantity' => $this->faker->numberBetween(20, 200),
            'type' => 'stock_in',
            'reference' => 'initial_seed',
            'notes' => 'Initial inventory',
        ];
    }
}