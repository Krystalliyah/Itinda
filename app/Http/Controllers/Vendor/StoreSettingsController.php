<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;

class StoreSettingsController extends Controller
{
    public function show()
    {
        $currentTenant = tenant();

        if (! $currentTenant) {
            abort(404, 'Tenant not found.');
        }

        $tenantRecord = Tenant::on('central')
            ->where('id', $currentTenant->id)
            ->firstOrFail();

        return inertia('vendor/StoreSettings', [
            'tenantInfo' => [
                'id' => $tenantRecord->id,
                'name' => $tenantRecord->name,
                'email' => $tenantRecord->email,
                'description' => $tenantRecord->description,
                'address' => $tenantRecord->address,
                'phone' => $tenantRecord->phone,
                'operating_hours' => $tenantRecord->operating_hours,
                'data' => $tenantRecord->data,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $currentTenant = tenant();

        if (! $currentTenant) {
            abort(404, 'Tenant not found.');
        }

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'operating_hours' => ['nullable', 'array'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'pickup_notes' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:255'],
            'pickup_lead_time' => ['nullable', 'string', 'max:100'],
            'order_notice' => ['nullable', 'string', 'max:100'],
        ]);

        $tenantRecord = Tenant::on('central')
            ->where('id', $currentTenant->id)
            ->firstOrFail();

        $existingData = is_array($tenantRecord->data) ? $tenantRecord->data : [];

        $tenantRecord->update([
            'name' => $validated['store_name'],
            'email' => $validated['email'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'operating_hours' => $validated['operating_hours'] ?? $tenantRecord->operating_hours,
            'data' => array_merge($existingData, [
                'tagline' => $validated['tagline'] ?? null,
                'pickup_notes' => $validated['pickup_notes'] ?? null,
                'website' => $validated['website'] ?? null,
                'pickup_lead_time' => $validated['pickup_lead_time'] ?? null,
                'order_notice' => $validated['order_notice'] ?? null,
            ]),
        ]);

        return back()->with('success', 'Store settings updated successfully.');
    }
}