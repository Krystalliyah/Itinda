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
                'profile_photo_path' => $tenantRecord->profile_photo_path,
                'cover_photo_path' => $tenantRecord->cover_photo_path,
                'profile_photo_url' => $tenantRecord->profile_photo_url,
                'cover_photo_url' => $tenantRecord->cover_photo_url,
            ],
        ]);
    }

    public function update(Request $request)
    {
        $currentTenant = tenant();

        if (! $currentTenant) {
            abort(404, 'Tenant not found.');
        }

        $tenantRecord = Tenant::on('central')
            ->where('id', $currentTenant->id)
            ->firstOrFail();

        // If only operating_hours is being sent, handle it separately
        if ($request->has('operating_hours') && count($request->all()) === 1) {
            try {
                $validated = $request->validate([
                    'operating_hours' => ['required', 'array'],
                    'operating_hours.*.is_open' => ['required', 'boolean'],
                    'operating_hours.*.open_time' => ['nullable', 'string'],
                    'operating_hours.*.close_time' => ['nullable', 'string'],
                ]);

                $tenantRecord->update([
                    'operating_hours' => $validated['operating_hours'],
                ]);

                return back()->with('success', 'Operating hours updated successfully.');
            } catch (\Exception $e) {
                \Log::error('Operating hours update failed', ['error' => $e->getMessage(), 'data' => $request->all()]);

                return back()->with('error', 'Failed to update operating hours: '.$e->getMessage());
            }
        }

        // Otherwise, validate all fields for full update
        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'address' => ['required', 'string'],
            'phone' => ['nullable', 'string', 'max:50'],
            'operating_hours' => ['nullable', 'array'],
            'operating_hours.*.is_open' => ['required', 'boolean'],
            'operating_hours.*.open_time' => ['nullable', 'string'],
            'operating_hours.*.close_time' => ['nullable', 'string'],
            'website' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'cover_photo' => ['nullable', 'image', 'max:5120'],
        ]);

        $existingData = is_array($tenantRecord->data) ? $tenantRecord->data : [];

        $updateData = [
            'name' => $validated['store_name'],
            'email' => $validated['email'],
            'description' => $validated['description'] ?? null,
            'address' => $validated['address'],
            'phone' => $validated['phone'] ?? null,
            'operating_hours' => $validated['operating_hours'] ?? null,
            'data' => array_merge($existingData, [
                'website' => $validated['website'] ?? null,
            ]),
        ];

        // Determine disk (S3 if available, otherwise public)
        $disk = config('filesystems.disks.s3.key') ? 's3' : 'public';

        if ($request->hasFile('profile_photo')) {
            // Delete old one if exists
            if ($tenantRecord->profile_photo_path) {
                \Storage::disk($disk)->delete($tenantRecord->profile_photo_path);
            }
            $updateData['profile_photo_path'] = $request->file('profile_photo')->store("branding/{$tenantRecord->id}", $disk);
        }

        if ($request->hasFile('cover_photo')) {
            // Delete old one if exists
            if ($tenantRecord->cover_photo_path) {
                \Storage::disk($disk)->delete($tenantRecord->cover_photo_path);
            }
            $updateData['cover_photo_path'] = $request->file('cover_photo')->store("branding/{$tenantRecord->id}", $disk);
        }

        $tenantRecord->update($updateData);

        return back()->with('success', 'Store settings updated successfully.');
    }
}
