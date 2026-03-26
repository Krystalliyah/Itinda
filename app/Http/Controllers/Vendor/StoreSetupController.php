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
            'description' => ['nullable', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'regex:/^(09|\+639)\d{9}$/'],
            'operating_hours' => ['required', 'array'],
            'operating_hours.*.is_open' => ['required', 'boolean'],
            'operating_hours.*.open_time' => ['nullable', 'string'],
            'operating_hours.*.close_time' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        $defaultHours = [
            'monday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '18:00'],
            'tuesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '18:00'],
            'wednesday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '18:00'],
            'thursday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '18:00'],
            'friday' => ['is_open' => true, 'open_time' => '08:00', 'close_time' => '18:00'],
            'saturday' => ['is_open' => true, 'open_time' => '09:00', 'close_time' => '15:00'],
            'sunday' => ['is_open' => false, 'open_time' => '09:00', 'close_time' => '15:00'],
        ];

        // Create tenant without auto-provisioning database
        $tenant = \App\Models\Tenant::create([
            'id' => \Illuminate\Support\Str::slug($validated['domain_slug']),
            'name' => $validated['store_name'],
            'email' => $user->email,
            'user_id' => $user->id,
            'is_approved' => false,
            'description' => $validated['description'],
            'address' => $validated['address'],
            'city' => $validated['city'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'operating_hours' => $validated['operating_hours'],
        ]);

        // Create domain: subdomain.itinda.test
        $tenant->domains()->create([
            'domain' => $validated['domain_slug'] . '.' . config('app.domain', 'localhost'),
        ]);

        return redirect()->route('vendor.dashboard')
            ->with('success', 'Store setup submitted. Awaiting admin approval.');
    }
}
