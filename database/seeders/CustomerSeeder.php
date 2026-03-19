<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // create 50 customers (adjust as needed)
        $users = User::factory()
            ->count(50)
            ->customer()
            ->create();

        // assign role using Spatie
        foreach ($users as $user) {
            $user->assignRole('customer'); // role_id = 3
        }
    }
}