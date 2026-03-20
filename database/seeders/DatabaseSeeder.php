<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdminUserSeeder::class,
            CustomerSeeder::class,
            CategorySeeder::class,
            // \Database\Seeders\Central\VendorSeeder::class, // Commented out - create vendors manually via admin panel
        ]);
        
    }
}