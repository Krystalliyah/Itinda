<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get all products from all stores with search, filter, and sort
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'sort_by' => 'nullable|in:name,price_low,price_high,rating,sold',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'in_stock' => 'nullable|boolean',
            'store_id' => 'nullable|integer',
        ]);

        $stores = Tenant::query()
            ->where('is_approved', 1)
            ->get();

        $allProducts = [];

        foreach ($stores as $store) {
            // Filter by store if specified
            if (isset($validated['store_id']) && $store->id != $validated['store_id']) {
                continue;
            }

            $storeProducts = $store->run(function () use ($validated) {
                $query = \App\Models\Product::query()
                    ->where('is_active', true)
                    ->with('category');

                // Search filter
                if (!empty($validated['search'])) {
                    $query->where(function ($q) use ($validated) {
                        $q->where('name', 'like', '%' . $validated['search'] . '%')
                          ->orWhere('description', 'like', '%' . $validated['search'] . '%')
                          ->orWhere('sku', 'like', '%' . $validated['search'] . '%');
                    });
                }

                // Category filter
                if (!empty($validated['category_id'])) {
                    $query->where('category_id', $validated['category_id']);
                }

                // Price range filter
                if (isset($validated['min_price'])) {
                    $query->where('price', '>=', $validated['min_price']);
                }
                if (isset($validated['max_price'])) {
                    $query->where('price', '<=', $validated['max_price']);
                }

                // Stock filter
                if (isset($validated['in_stock']) && $validated['in_stock']) {
                    $query->where('stock', '>', 0);
                }

                return $query->get();
            });

            foreach ($storeProducts as $product) {
                $allProducts[] = [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'description' => $product->description ?? '',
                    'category_id' => $product->category_id,
                    'category_name' => $product->category->name ?? null,
                    'image_url' => $product->image_path 
                        ? Storage::disk('s3')->url($product->image_path) 
                        : 'https://picsum.photos/400?random=' . $product->id,
                    'unit_price' => (float) $product->price,
                    'stock_level' => $product->stock ?? 0,
                    'sold_count' => 0, // TODO: Calculate from orders
                    'rating' => 4.5, // TODO: Calculate from reviews
                    'is_available' => $product->stock > 0,
                    'is_active' => $product->is_active,
                    'store' => [
                        'id' => $store->id,
                        'name' => $store->name,
                        'logo' => $store->logo ?? 'https://ui-avatars.com/api/?name=' . urlencode($store->name),
                    ],
                ];
            }
        }

        // Apply sorting
        $sortBy = $validated['sort_by'] ?? 'name';
        usort($allProducts, function ($a, $b) use ($sortBy) {
            switch ($sortBy) {
                case 'price_low':
                    return $a['unit_price'] <=> $b['unit_price'];
                case 'price_high':
                    return $b['unit_price'] <=> $a['unit_price'];
                case 'rating':
                    return $b['rating'] <=> $a['rating'];
                case 'sold':
                    return $b['sold_count'] <=> $a['sold_count'];
                case 'name':
                default:
                    return strcasecmp($a['product_name'], $b['product_name']);
            }
        });

        return response()->json([
            'data' => $allProducts,
            'meta' => [
                'total' => count($allProducts),
                'filters_applied' => array_filter($validated),
            ]
        ]);
    }

    /**
     * Get products for a specific store with filters
     */
    public function storeProducts(Request $request, $storeId)
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer',
            'sort_by' => 'nullable|in:name,price_low,price_high,rating,sold',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'in_stock' => 'nullable|boolean',
        ]);

        $store = Tenant::query()
            ->where('is_approved', 1)
            ->findOrFail($storeId);

        $products = $store->run(function () use ($validated) {
            $query = \App\Models\Product::query()
                ->where('is_active', true)
                ->with('category');

            // Search filter
            if (!empty($validated['search'])) {
                $query->where(function ($q) use ($validated) {
                    $q->where('name', 'like', '%' . $validated['search'] . '%')
                      ->orWhere('description', 'like', '%' . $validated['search'] . '%')
                      ->orWhere('sku', 'like', '%' . $validated['search'] . '%');
                });
            }

            // Category filter
            if (!empty($validated['category_id'])) {
                $query->where('category_id', $validated['category_id']);
            }

            // Price range filter
            if (isset($validated['min_price'])) {
                $query->where('price', '>=', $validated['min_price']);
            }
            if (isset($validated['max_price'])) {
                $query->where('price', '<=', $validated['max_price']);
            }

            // Stock filter
            if (isset($validated['in_stock']) && $validated['in_stock']) {
                $query->where('stock', '>', 0);
            }

            // Apply sorting
            $sortBy = $validated['sort_by'] ?? 'name';
            switch ($sortBy) {
                case 'price_low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_high':
                    $query->orderBy('price', 'desc');
                    break;
                case 'name':
                default:
                    $query->orderBy('name', 'asc');
                    break;
            }

            return $query->get()->map(function ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'description' => $product->description ?? '',
                    'category_id' => $product->category_id,
                    'category_name' => $product->category->name ?? null,
                    'image_url' => $product->image_path 
                        ? Storage::disk('s3')->url($product->image_path) 
                        : 'https://picsum.photos/400?random=' . $product->id,
                    'unit_price' => (float) $product->price,
                    'stock_level' => $product->stock ?? 0,
                    'sold_count' => 0,
                    'rating' => 4.5,
                    'is_available' => $product->stock > 0,
                    'is_active' => $product->is_active,
                ];
            });
        });

        return response()->json([
            'data' => $products,
            'meta' => [
                'total' => $products->count(),
                'store_id' => $storeId,
                'filters_applied' => array_filter($validated),
            ]
        ]);
    }
    /**
         * Get a single product from a specific store
         */
        public function show($storeId, $productId)
        {
            $store = Tenant::query()
                ->where('is_approved', 1)
                ->findOrFail($storeId);

            $product = $store->run(function () use ($productId) {
                return \App\Models\Product::with('category')->findOrFail($productId);
            });

            return response()->json([
                'data' => [
                    'id'            => $product->id,
                    'product_name'  => $product->name,
                    'description'   => $product->description ?? '',
                    'sku'           => $product->sku ?? null,
                    'category_id'   => $product->category_id,
                    'category_name' => $product->category->name ?? null,
                    'image_url'     => $product->image_path
                        ? Storage::disk('s3')->url($product->image_path)
                        : 'https://picsum.photos/600?random=' . $product->id,
                    'unit_price'    => (float) $product->price,
                    'stock_level'   => $product->stock ?? 0,
                    'sold_count'    => 0,
                    'rating'        => 4.5,
                    'is_available'  => $product->stock > 0,
                    'is_active'     => $product->is_active,
                    'store' => [
                        'id'   => $store->id,
                        'name' => $store->name,
                        'logo' => $store->logo ?? 'https://ui-avatars.com/api/?name=' . urlencode($store->name),
                    ],
                ],
            ]);
        }
}
