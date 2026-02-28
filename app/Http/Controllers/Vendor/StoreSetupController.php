<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use Illuminate\Http\Request;

class StoreSetupController extends Controller
{
    /**
     * Store the new store
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'domain_slug' => ['required', 'string', 'max:63', 'regex:/^[a-z0-9-]+$/', 'unique:domains,domain'],
            'address' => ['required', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'operating_hours' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();

        // Create tenant without auto-provisioning database
        $tenant = \App\Models\Tenant::create([
            'id' => \Illuminate\Support\Str::slug($validated['domain_slug']),
            'name' => $validated['store_name'],
            'email' => $user->email,
            'user_id' => $user->id,
            'is_approved' => false,
        ]);

        // Create domain: subdomain.storekoto.test
        $tenant->domains()->create([
            'domain' => $validated['domain_slug'] . '.' . config('app.domain', 'localhost'),
        ]);

        return redirect()->route('vendor.dashboard')
            ->with('success', 'Store setup submitted. Awaiting admin approval.');
    }
}
