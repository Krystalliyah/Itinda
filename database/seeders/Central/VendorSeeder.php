<?php

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

use App\Models\User;
use App\Models\Tenant;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $domainBase = config('app.domain', 'itinda.test');

        $numberOfVendors = 5; // Adjust as needed

        for ($i = 0; $i < $numberOfVendors; $i++) {

            // Generate vendor/store name
            $storeName = $faker->company . ' Store';

            // Generate subdomain
            $subdomain = Str::slug($storeName);

            // Generate email
            $email = $faker->unique()->safeEmail();

            // Create user
            $user = User::create([
                'name' => $faker->name(),
                'email' => $email,
                'phone' => $faker->phoneNumber(),
                'password' => Hash::make('Password123!'), // Default password for all vendors
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            // Assign vendor role
            $user->assignRole('vendor');

            // Define the operating hours array
            $operatingHours = [
                "monday" => ["is_open" => true, "open_time" => "08:00", "close_time" => "18:00"],
                "tuesday" => ["is_open" => true, "open_time" => "08:00", "close_time" => "18:00"],
                "wednesday" => ["is_open" => true, "open_time" => "08:00", "close_time" => "18:00"],
                "thursday" => ["is_open" => true, "open_time" => "08:00", "close_time" => "18:00"],
                "friday" => ["is_open" => true, "open_time" => "08:00", "close_time" => "18:00"],
                "saturday" => ["is_open" => false, "open_time" => "09:00", "close_time" => "15:00"],
                "sunday" => ["is_open" => false, "open_time" => "09:00", "close_time" => "15:00"],
            ];
            // Create tenant
            $tenant = Tenant::create([
                'id' => $subdomain,
                'name' => $storeName,
                'description' => null,
                'email' => $email,
                'is_approved' => false,
                'user_id' => $user->id,
                'operating_hours' => $operatingHours,
            ]);

            // Create domain
            $tenant->domains()->create([
                'domain' => "{$subdomain}.{$domainBase}",
            ]);
        }
    }
}