<?php

/**
 * TEMPORARY SEEDER
 *
 * Syncs existing users.role enum values into Spatie roles.
 * This should only be run once during migration to Spatie.
 * After registration flow assigns roles via Spatie directly,
 * this seeder will no longer be needed.
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class SyncUserEnumRolesSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->chunkById(200, function ($users) {
            foreach ($users as $user) {
                if ($user->role) {
                    $user->syncRoles([$user->role]); // admin/vendor/customer
                }
            }
        });
    }
}
