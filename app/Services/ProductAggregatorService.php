<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductAggregatorService
{
    /**
     * Get all products from all tenants for customer browsing
     */
    public function getAllProducts(array $filters = []): Collection
    {
        $allProducts = collect();

        Tenant::with('domains')->get()->each(function ($tenant) use (&$allProducts, $filters) {
            // Initialize tenancy to switch to tenant's database
            tenancy()->initialize($tenant);

            try {
                // Query products from this tenant's database
                $query = DB::table('products')
                    ->select([
                        'id',
                        'name',
                        'description',
                        'price',
                        'stock_quantity',
                        'category_id',
                        'image_url',
                        'created_at',
                    ])
                    ->where('stock_quantity', '>', 0); // Only show in-stock items

                // Apply filters
                if (!empty($filters['category_id'])) {
                    $query->where('category_id', $filters['category_id']);
                }

                if (!empty($filters['search'])) {
                    $query->where(function ($q) use ($filters) {
                        $q->where('name', 'like', '%' . $filters['search'] . '%')
                          ->orWhere('description', 'like', '%' . $filters['search'] . '%');
                    });
                }

                if (!empty($filters['min_price'])) {
                    $query->where('price', '>=', $filters['min_price']);
                }

                if (!empty($filters['max_price'])) {
                    $query->where('price', '<=', $filters['max_price']);
                }

                $products = $query->get();

                // Add tenant information to each product
                $products->each(function ($product) use ($tenant) {
                    $product->tenant_id = $tenant->id;
                    $product->tenant_name = $tenant->name;
                    $product->tenant_domain = $tenant->domains->first()->domain ?? null;
                });

                $allProducts = $allProducts->merge($products);
            } catch (\Exception $e) {
                // Skip tenants with missing tables or other errors
                \Log::warning("Failed to fetch products from tenant {$tenant->id}: {$e->getMessage()}");
            } finally {
                // Always end tenancy to return to central database
                tenancy()->end();
            }
        });

        return $allProducts;
    }

    /**
     * Get products grouped by tenant
     */
    public function getProductsByTenant(array $filters = []): Collection
    {
        return $this->getAllProducts($filters)->groupBy('tenant_id');
    }

    /**
     * Search products across all tenants
     */
    public function searchProducts(string $query): Collection
    {
        return $this->getAllProducts(['search' => $query]);
    }

    /**
     * Get all categories from all tenants
     */
    public function getAllCategories(): Collection
    {
        $allCategories = collect();

        Tenant::get()->each(function ($tenant) use (&$allCategories) {
            tenancy()->initialize($tenant);

            try {
                $categories = DB::table('categories')
                    ->select(['id', 'name', 'description'])
                    ->get();

                $categories->each(function ($category) use ($tenant) {
                    $category->tenant_id = $tenant->id;
                    $category->tenant_name = $tenant->name;
                });

                $allCategories = $allCategories->merge($categories);
            } catch (\Exception $e) {
                \Log::warning("Failed to fetch categories from tenant {$tenant->id}: {$e->getMessage()}");
            } finally {
                tenancy()->end();
            }
        });

        return $allCategories->unique('name');
    }
}
