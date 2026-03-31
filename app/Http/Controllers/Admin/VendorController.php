<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantAuthToken;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;

class VendorController extends Controller
{
    public function index()
    {
        $tenants = Tenant::with('domains')->latest()->get();

        return inertia('admin/Vendors', [
            'tenants' => [
                'data' => $tenants
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subdomain' => 'required|string|max:63|regex:/^[a-z0-9-]+$/',
        ]);

        // Create tenant without auto-provisioning database
        $tenant = Tenant::create([
            'id' => Str::slug($validated['subdomain']),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_approved' => false,
        ]);

        // Create domain: subdomain.itinda.test
        $tenant->domains()->create([
            'domain' => $validated['subdomain'] . '.' . config('app.domain', 'localhost'),
        ]);

        return back()->with('success', "Vendor created and is pending approval!");
    }

    public function approve(Tenant $tenant)
    {
        // Set approved
        $tenant->update(['is_approved' => true]);

        // Run the Database creation and migration manually
        dispatch_sync(new CreateDatabase($tenant));
        dispatch_sync(new MigrateDatabase($tenant));

        // Create the tenant admin user from the vendor email
        //$this->copyVendorUserToTenant($tenant);

        // Run tenant-specific seeders (role table + admin user).
        $tenant->run(function () {
            (new \Database\Seeders\Tenant\TenantRolesSeeder())->run();
            (new \Database\Seeders\Tenant\TenantStoreAdminSeeder())->run();
        });

        // Generate a one-time authentication token for SSO
        $token = $this->generateTenantAuthToken($tenant);

        // Build the tenant URL with the token
        $tenantDomain = $tenant->domains->first()?->domain;
        $tenantUrl = "https://{$tenantDomain}/?sso_token={$token}";

        // Show credentials in flash message for convenience
        $loginId = "admin-{$tenant->id}";

        return back()->with('vendor_approved', [
            'name'     => $tenant->name,
            'url'      => $tenantUrl,
            'login_id' => $loginId,
        ]);
    }

    /**
     * Copy the vendor's central user to the tenant database
     */
    protected function copyVendorUserToTenant(Tenant $tenant): void
    {
        // Find the user with the same email as the tenant
        $centralUser = User::where('email', $tenant->email)->first();

        if (!$centralUser) {
            return; // No user to copy
        }

        // Initialize tenancy for this tenant
        tenancy()->initialize($tenant);

        try {
            // Check if user already exists in tenant DB
            $existingUser = User::where('email', $centralUser->email)->first();

            if (!$existingUser) {
                // Create the tenant user with the same credentials
                $tenantUser = User::create([
                    'name' => $centralUser->name,
                    'login_id' => $centralUser->email ?? 'vendor-' . $tenant->id,
                    'email' => $centralUser->email,
                    'password' => $centralUser->password, // The bcrypt hash is portable
                    'phone' => $centralUser->phone,
                    'email_verified_at' => $centralUser->email_verified_at,
                    'is_admin' => false,
                ]);

                // Assign vendor role in the tenant if it exists
                if (\Spatie\Permission\Models\Role::where('name', 'vendor')->exists()) {
                    $tenantUser->assignRole('vendor');
                }
            }
        } finally {
            // Revert to central context
            tenancy()->end();
        }
    }

    /**
     * Generate a one-time SSO token for tenant access
     */
    protected function generateTenantAuthToken(Tenant $tenant): string
    {
        // Get the central user again for the token
        $centralUser = User::where('email', $tenant->email)->first();

        if (!$centralUser) {
            return '';
        }

        $token = Str::uuid()->toString();

        TenantAuthToken::create([
            'token' => $token,
            'tenant_id' => $tenant->id,
            'authenticatable_id' => $centralUser->id,
            'authenticatable_type' => User::class,
            'expires_at' => now()->addMinutes(5), // Token valid for 5 minutes
        ]);

        return $token;
    }


    /**
     * Seed the tenant database with a default test user.
     *
     * This runs the TenantTestUserSeeder inside the tenant context
     * to populate the tenant's users table with a default test user.
     * Idempotency is handled by the seeder itself (checks if test user exists).
     */
    protected function seedTenantDatabase(Tenant $tenant): void
    {
        try {
            $tenant->run(function () {
                \Illuminate\Support\Facades\Artisan::call("db:seed", [
                    "--class" => "Database\Seeders\Tenant\TenantTestUserSeeder",
                ]);
            });
        } catch (\Exception $e) {
            \Log::warning("Failed to seed tenant {$tenant->id}: {$e->getMessage()}");
            // Don't throw; allow approval to continue even if seeding fails
        }
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete(); // This should trigger the DeleteDatabase job pipeline automatically if set in TenancyServiceProvider
        return back()->with('success', 'Vendor and database deleted!');
    }
}

