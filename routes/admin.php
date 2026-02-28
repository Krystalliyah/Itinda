<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\VendorController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;

Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/categories', fn() => inertia('admin/Categories'))->name('categories');
    Route::get('/reports', fn() => inertia('admin/Reports'))->name('reports');
    
    // Vendor management - simple CRUD and Approval
    Route::get('/vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::post('/vendors', [VendorController::class, 'store'])->name('vendors.store');
    Route::post('/vendors/{tenant}/approve', [VendorController::class, 'approve'])->name('vendors.approve');
    Route::delete('/vendors/{tenant}', [VendorController::class, 'destroy'])->name('vendors.destroy');
});
