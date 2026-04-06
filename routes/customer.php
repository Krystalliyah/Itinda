<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;

Route::middleware(['auth', 'verified', 'role:customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::controller(\App\Http\Controllers\Customer\CustomerPageController::class)->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');
            Route::get('/stores', 'stores')->name('stores');
            Route::get('/stores/{id}', 'showStore')->name('stores.show');
            Route::get('/products', 'products')->name('products');
            Route::get('/products/{storeId}/{productId}', 'showProduct')->name('products.show');
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
        Route::get('/products-data/{storeId}/{productId}', [ProductController::class, 'show']);
        Route::get('/products-data/store/{id}', [ProductController::class, 'storeProducts']);

       // Product Review Routes - stats MUST be before index
        Route::get('/products/{storeId}/{productId}/reviews/stats', [App\Http\Controllers\Api\ProductReviewController::class, 'stats']);
        Route::get('/products/{storeId}/{productId}/reviews', [App\Http\Controllers\Api\ProductReviewController::class, 'index']);
        Route::post('/products/{storeId}/{productId}/reviews', [App\Http\Controllers\Api\ProductReviewController::class, 'store'])->middleware('auth');
        Route::post('/reviews/{storeId}/{reviewId}/helpful', [App\Http\Controllers\Api\ProductReviewController::class, 'helpful'])->middleware('auth');
                
        // Categories API Route
        Route::get('/categories', [CategoryController::class, 'index']);
        
        // Cart API Routes
        Route::get('/cart-data', [CartController::class, 'index']);
        Route::post('/cart/add', [CartController::class, 'add']);
        Route::post('/cart/preorder', [CartController::class, 'preorder']);
        Route::delete('/cart/bulk', [CartController::class, 'destroyMany']);
        Route::delete('/cart', [CartController::class, 'clear']);
        Route::delete('/cart/remove-all', [CartController::class, 'removeAll']);
        Route::put('/cart/{cart}', [CartController::class, 'update']);
        Route::delete('/cart/{cart}', [CartController::class, 'destroy']);

        // Orders Page API Routes
        Route::get('/orders-data', [CustomerOrderController::class, 'index'])->name('orders.data');
        Route::get('/orders-data/{id}', [CustomerOrderController::class, 'show'])->name('orders.show.data');
        Route::post('/orders/{id}/cancel', [CustomerOrderController::class, 'cancel'])->name('orders.cancel');
});
