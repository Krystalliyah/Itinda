<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ProductAggregatorService;
use Illuminate\Support\Facades\Cache;

class CustomerPageController extends Controller
{
    public function dashboard()
    {
        $proof = Cache::remember('tenant_products_proof', 60, function () {
            return app(ProductAggregatorService::class)->tenantProductsProof();
        });

        return inertia('customer/Dashboard', [
            'tenantProductsProof' => $proof,
            'showDebugPanel' => config('app.debug'),
        ]);
    }

    public function stores()
    {
        return inertia('customer/Stores');
    }

    public function showStore($id)
    {
        return inertia('customer/StoreDetails', [
            'storeId' => $id,
        ]);
    }

    public function products()
    {
        return inertia('customer/Products');
    }

    public function orders()
    {
        return inertia('customer/Orders');
    }

    public function profile()
    {
        return inertia('customer/Profile');
    }

    public function cart()
    {
        return inertia('customer/Cart');
    }
}
