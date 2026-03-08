<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

use App\Http\Controllers\Vendor\StoreSetupController;
use App\Http\Controllers\Vendor\ProductController;
use App\Models\Product;

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    'consume-tenant-auth-token',
])->group(function () {
    // Tenant root: guests go to local login, authenticated users to dashboard
    Route::get('/', function () {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return redirect('/vendor/dashboard');
    });

    // Authentication pages on tenant
    Route::get('/login', function () {
        return Inertia::render('auth/LoginAdminTenant');
    })->name('tenant.login');

    Route::get('/vendor-admin/login', function () {
        return Inertia::render('auth/LoginAdminTenant');
    });

    // Basic vendor routes (accessible to any vendor account)
    Route::middleware(['auth', 'verified', 'role:vendor'])->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', function() {
            // Get products from THIS tenant's database
            $products = Product::latest()->take(10)->get();
            $tenant = tenant();

            return inertia('vendor/Dashboard', [
                'tenantInfo' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'database' => 'tenant' . $tenant->id,
                ],
                'products' => $products,
                'productCount' => Product::count(),
            ]);
        })->name('dashboard');
        Route::post('/store/setup', [StoreSetupController::class, 'store'])->name('store.create');
    });

    Route::middleware(['auth', 'verified', 'role:vendor', 'vendor.is_approved'])->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        
        Route::get('/inventory', fn() => inertia('vendor/Inventory'))->name('inventory');
        Route::get('/orders', fn() => inertia('vendor/Orders'))->name('orders');

        // admin-only
        Route::get('/store-settings', fn() => inertia('vendor/StoreSettings'))->name('store.settings')->middleware('vendor.is_admin');
        Route::get('/staff', fn() => inertia('vendor/Staff'))->name('staff')->middleware('vendor.is_admin');
        
        Route::get('/expenses', fn() => inertia('vendor/Expenses'))->name('expenses');
        Route::get('/analytics', fn() => inertia('vendor/Analytics'))->name('analytics');
    });
});
