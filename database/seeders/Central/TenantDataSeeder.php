<?php

namespace Database\Seeders\Central;

use Illuminate\Database\Seeder;
use App\Models\Tenant;

class TenantDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::where('is_approved', true)->get();

        foreach ($tenants as $tenant) {

            $this->command->info("Seeding tenant: {$tenant->id}");

            $tenant->run(function () use ($tenant) {
                fake()->seed(crc32($tenant->id)); 
                (new \Database\Seeders\Tenant\TenantMockDataSeeder())->run();
            });

        }
    }
}