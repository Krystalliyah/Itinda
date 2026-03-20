<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::get('/reports', [App\Http\Controllers\Admin\ReportsController::class, 'index'])->name('reports');
    
    // Vendor management - simple CRUD and Approval
    Route::apiResource('vendors', VendorController::class)
        ->parameters(['vendors' => 'tenant'])
        ->only(['index', 'store', 'destroy']);
    Route::post('/vendors/{tenant}/approve', [VendorController::class, 'approve'])->name('vendors.approve');
});
