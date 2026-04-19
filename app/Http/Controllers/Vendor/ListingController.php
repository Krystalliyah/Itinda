<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ListingController extends Controller
{
    public function index()
    {
        $listings = Listing::query()
            ->withCount('products')
            ->latest()
            ->paginate(15)
            ->through(fn ($listing) => [
                'id' => $listing->id,
                'name' => $listing->name,
                'description' => $listing->description,
                'slug' => $listing->slug,
                'cover_image_path' => $listing->cover_image_path,
                'is_active' => (bool) $listing->is_active,
                'products_count' => $listing->products_count,
                'created_at' => $listing->created_at,
            ]);

        return inertia('vendor/Listings', [
            'listings' => $listings,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name'].'-'.Str::random(5));
        $validated['is_active'] = $request->boolean('is_active', true);

        Listing::create($validated);

        return back()->with('success', 'Listing created successfully.');
    }

    public function update(Request $request, Listing $listing)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name'].'-'.Str::random(5));
        $validated['is_active'] = $request->boolean('is_active', true);

        $listing->update($validated);

        return back()->with('success', 'Listing updated successfully.');
    }

    public function destroy(Listing $listing)
    {
        $listing->products()->detach();
        $listing->delete();

        return back()->with('success', 'Listing deleted successfully.');
    }
}