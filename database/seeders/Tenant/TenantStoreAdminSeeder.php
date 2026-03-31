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

        $user = User::where('login_id', $loginId)->first();

        if (! $user) {
            return;
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
