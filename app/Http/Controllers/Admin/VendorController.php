<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
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

        // Create domain: subdomain.storekoto.test
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

        return back()->with('success', "Vendor {$tenant->name} approved! Database provisioned.");
    }

    public function destroy(Tenant $tenant)
    {
        $tenant->delete(); // This should trigger the DeleteDatabase job pipeline automatically if set in TenancyServiceProvider
        return back()->with('success', 'Vendor and database deleted!');
    }
}
