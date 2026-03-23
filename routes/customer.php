<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerOrderController;

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::controller(\App\Http\Controllers\Customer\CustomerPageController::class)->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/stores', 'stores')->name('stores');
            Route::get('/stores/{id}', 'showStore')->name('stores.show');
            Route::get('/products', 'products')->name('products');
            Route::get('/orders', 'orders')->name('orders');
            Route::get('/profile', 'profile')->name('profile');
            Route::get('/cart', 'cart')->name('cart');
        });

        // Stores Page API Routes
        Route::get('/stores-data', [StoreController::class, 'index']);
        Route::get('/stores-data/{id}', [StoreController::class, 'show']);
        Route::get('/stores-data/{id}/products', [StoreController::class, 'products']);
        
        // Products API Routes (with search, filter, sort)
        Route::get('/products-data', [ProductController::class, 'index']);
        Route::get('/products-data/store/{id}', [ProductController::class, 'storeProducts']);
        
        // Categories API Route
        Route::get('/categories', [\App\Http\Controllers\Api\CategoryController::class, 'index']);
        
        // Cart API Routes
        Route::get('/cart-data', [\App\Http\Controllers\Api\CartController::class, 'index']);
        Route::post('/cart/add', [\App\Http\Controllers\Api\CartController::class, 'add']);
        Route::put('/cart/{cart}', [\App\Http\Controllers\Api\CartController::class, 'update']);
        Route::delete('/cart/bulk', [\App\Http\Controllers\Api\CartController::class, 'destroyMany']);
        Route::delete('/cart/{cart}', [\App\Http\Controllers\Api\CartController::class, 'destroy']);
        Route::delete('/cart', [\App\Http\Controllers\Api\CartController::class, 'clear']);

        // Orders Page API Routes
        Route::get('/orders-data', [CustomerOrderController::class, 'index'])->name('orders.data');
        Route::get('/orders-data/{id}', [CustomerOrderController::class, 'show'])->name('orders.show.data');
});
