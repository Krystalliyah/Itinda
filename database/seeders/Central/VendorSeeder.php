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
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            // Assign vendor role
            $user->assignRole('vendor');

            // Create tenant
            $tenant = Tenant::create([
                'id' => $subdomain,
                'name' => $storeName,
                'email' => $email,
                'is_approved' => false,
                'user_id' => $user->id,
            ]);

            // Create domain
            $tenant->domains()->create([
                'domain' => "{$subdomain}.{$domainBase}",
            ]);
        }
    }
}