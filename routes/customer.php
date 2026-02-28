<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\ProductBrowseController;

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/dashboard', fn() => inertia('customer/Dashboard'))->name('dashboard');
    Route::get('/stores', fn() => inertia('customer/Stores'))->name('stores');
    
    // Product browsing across all tenants
    Route::get('/products', [ProductBrowseController::class, 'index'])->name('products');
    Route::get('/products/search', [ProductBrowseController::class, 'search'])->name('products.search');
    Route::get('/products/by-vendor', [ProductBrowseController::class, 'byVendor'])->name('products.by-vendor');
    
    Route::get('/orders', fn() => inertia('customer/Orders'))->name('orders');
    Route::get('/profile', fn() => inertia('customer/Profile'))->name('profile');
});
