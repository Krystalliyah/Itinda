<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ProductAggregatorService;
use Illuminate\Http\Request;

class ProductBrowseController extends Controller
{
    public function __construct(
        protected ProductAggregatorService $aggregator
    ) {}

    /**
     * Show all products from all tenants
     */
    public function index(Request $request)
    {
        $filters = $request->only(['category_id', 'search', 'min_price', 'max_price']);
        
        $products = $this->aggregator->getAllProducts($filters);
        $categories = $this->aggregator->getAllCategories();

        // Sort by newest first
        $products = $products->sortByDesc('created_at')->values();

        return inertia('customer/Products/Browse', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    /**
     * Search products across all tenants
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');
        
        $products = $this->aggregator->searchProducts($query);
        
        return response()->json([
            'products' => $products->values(),
            'count' => $products->count(),
        ]);
    }

    /**
     * Show products grouped by vendor/tenant
     */
    public function byVendor(Request $request)
    {
        $filters = $request->only(['category_id', 'search', 'min_price', 'max_price']);
        
        $productsByTenant = $this->aggregator->getProductsByTenant($filters);
        $categories = $this->aggregator->getAllCategories();

        return inertia('customer/Products/ByVendor', [
            'productsByTenant' => $productsByTenant,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }
}
