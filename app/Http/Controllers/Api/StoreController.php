<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Tenant;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Tenant::query()
            ->where('is_approved', 1)
            ->latest()
            ->get();

        return StoreResource::collection($stores);
    }

    public function show($id)
    {
        $store = Tenant::query()
            ->where('is_approved', 1)
            ->findOrFail($id);

        return new StoreResource($store);
    }

    public function products($id)
    {
        $store = Tenant::query()
            ->where('is_approved', 1)
            ->findOrFail($id);

        // Run query in tenant database
        $products = $store->run(function () {
            return \App\Models\Product::where('is_active', true)
                ->with('category')
                ->latest()
                ->get()
                ->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_name' => $product->name,
                        'description' => $product->description ?? '',
                        'category_id' => $product->category_id,
                        'image_url' => $product->image_path ? asset('storage/' . $product->image_path) : 'https://picsum.photos/400?random=' . $product->id,
                        'unit_price' => (float) $product->price,
                        'stock_level' => $product->stock ?? 0,
                        'sold_count' => 0, // You can calculate this from orders if needed
                        'rating' => 4.5, // You can calculate this from reviews if needed
                        'is_available' => $product->stock > 0,
                        'is_active' => $product->is_active,
                    ];
                });
        });

        return response()->json([
            'data' => $products
        ]);
    }
}