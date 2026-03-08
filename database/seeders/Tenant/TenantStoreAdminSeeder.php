<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantStoreAdminSeeder extends Seeder
{
    /**
     * Run the tenant store admin seeder.
     */
    public function run(): void
    {
        $tenant = tenant();
        if (! $tenant) {
            return;
        }

        $loginId = 'admin-' . $tenant->id;
        $email = $loginId . '@tenant.local';

        $user = User::where('login_id', $loginId)->first();

        if (! $user) {
            $user = User::create([
                'name' => 'Store Admin',
                'login_id' => $loginId,
                'email' => $email,
                'password' => Hash::make('password'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]);
        }
        
        if (!$user->email_verified_at) {
            $user->email_verified_at = now();
            $user->save();
        }

        if (! $user->hasRole('vendor')) {
            $user->assignRole('vendor');
        }

        if (! $user->is_admin) {
            $user->is_admin = true;
            $user->save();
        }
    }
}
