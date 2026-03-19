<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreController;

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
});