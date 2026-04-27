<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Store;
use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use App\Support\ChecksStoreReadiness;

class StoreController extends Controller
{
    use ChecksStoreReadiness;

    /**
     * Resolve a product image URL — local first, then S3, then placeholder.
     */
    private function getProductImageUrl($product): ?string
    {
        if (!$product->image_path) {
            return 'https://picsum.photos/400?random=' . $product->id;
        }

        // Already a full URL
        if (str_starts_with($product->image_path, 'http')) {
            return $product->image_path;
        }

        // Check local public disk first (seeder / dev images)
        if (Storage::disk('public')->exists($product->image_path)) {
            return Storage::disk('public')->url($product->image_path);
        }

        // Not local — try S3 (real vendor uploads)
        if (config('filesystems.default') === 's3' || config('filesystems.cloud') === 's3') {
            return Storage::disk('s3')->url($product->image_path);
        }

        // Final fallback
        return asset('storage/' . $product->image_path);
    }

    public function index()
    {
        $stores = Tenant::query()
            ->where('is_approved', 1)
            ->latest()
            ->get()
            ->filter(fn ($store) => $this->isStoreReady($store))
            ->values();

        return StoreResource::collection($stores);
    }

    public function show($id)
    {
        $store = Tenant::query()
            ->where('id', $id)
            ->where('is_approved', 1)
            ->firstOrFail();

        abort_unless($this->isStoreReady($store), 404, 'Store not found');

        return new StoreResource($store);
    }

    public function products($id)
    {
        $store = Tenant::query()
            ->where('id', $id)
            ->where('is_approved', 1)
            ->firstOrFail();

        abort_unless($this->isStoreReady($store), 404, 'Store not found');

        $self = $this;

        // Run query in tenant database
        $products = $store->run(function () use ($self) {
            return \App\Models\Product::where('is_active', true)
                ->with('category')
                ->latest()
                ->get()
                ->map(function ($product) use ($self) {
                    return [
                        'id'           => $product->id,
                        'product_name' => $product->name,
                        'description'  => $product->description ?? '',
                        'category_id'  => $product->category_id,
                        'image_url'    => $self->getProductImageUrl($product),
                        'unit_price'   => (float) $product->price,
                        'stock_level'  => $product->stock ?? 0,
                        'sold_count'   => 0,
                        'rating'       => 4.5,
                        'is_available' => $product->stock > 0,
                        'is_active'    => $product->is_active,
                    ];
                });
        });

        return response()->json([
            'data' => $products
        ]);
    }


    // In StoreController.php
    public function getStoresData()
    {
        $stores = Store::select('id', 'name', 'address', 'phone', 'hours', 'is_open as isOpen', 'logo', 'cover')
            ->get();
        
        return response()->json(['data' => $stores]);
    }
}