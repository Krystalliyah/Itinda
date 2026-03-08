<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', function () {
        $proof = \Illuminate\Support\Facades\Cache::remember('tenant_products_proof', 60, function () {
            return app(\App\Services\ProductAggregatorService::class)->tenantProductsProof();
        });

        return inertia('customer/Dashboard', [
            'tenantProductsProof' => $proof,
            'showDebugPanel' => config('app.debug'),
        ]);
    })->name('dashboard');
    Route::get('/stores', fn() => inertia('customer/Stores'))->name('stores');
    Route::get('/products', fn() => inertia('customer/Products'))->name('products');
    Route::get('/orders', fn() => inertia('customer/Orders'))->name('orders');
    Route::get('/profile', fn() => inertia('customer/Profile'))->name('profile');
    
    Route::get('/stores/{id}', function ($id) {
        return inertia('customer/StoreDetails', [
            'storeId' => $id,
        ]);
    })->name('stores.show');

    Route::get('/cart', fn() => inertia('customer/Cart'))->name('cart');
});