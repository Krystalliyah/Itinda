<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Inertia\Inertia;


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


use App\Http\Controllers\Vendor\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ReportsController;

use App\Models\Product;

Route::middleware([
    InitializeTenancyByDomain::class,
    'web',
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
        return Inertia::render('auth/LoginTenant');
    })->name('login');

    Route::get('/vendor-admin/login', function () {
        return Inertia::render('auth/LoginTenant');
    });

    require __DIR__.'/settings.php';

/*
|--------------------------------------------------------------------------
| TENANT DOMAIN VENDOR ROUTES ( tenant.php )
|--------------------------------------------------------------------------
| This file handles routes specifically on the TENANT SUBDOMAIN (e.g., foo.itinda.test).
| All routes in this file interact with the tenant's isolated database.
|
| IMPORTANT: Central domain activities (like initially creating the store profile) 
| MUST NOT be placed here, as those actions belong in your central database. 
| Central routes belong in routes/vendor.php.
|--------------------------------------------------------------------------
*/

    // Basic vendor routes (accessible to any vendor account or staff)
    Route::middleware(['auth', 'verified', 'role:vendor|staff'])->prefix('vendor')->name('vendor.')->group(function () {
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
        Route::get('/profile', fn() => inertia('vendor/Profile'))->name('profile');
    });

    Route::middleware(['auth', 'verified', 'role:vendor|staff', 'vendor.is_approved'])->prefix('vendor')->name('vendor.')->group(function () {
        // Products management
        Route::middleware('permission:manage-products')->group(function() {
            Route::apiResource('products', ProductController::class)->except(['show']);
        });
        
        Route::get('/inventory', fn() => inertia('vendor/Inventory'))->name('inventory')->middleware('permission:manage-inventory');
        Route::get('/orders', fn() => inertia('vendor/Orders'))->name('orders')->middleware('permission:manage-orders');

        // Management routes
        Route::get('/store-settings', fn() => inertia('vendor/StoreSettings'))->name('store.settings')->middleware('permission:manage-store-settings');
        
        // Staff Management
        Route::middleware('permission:manage-staff')->group(function() {
            Route::apiResource('staff', App\Http\Controllers\Vendor\StaffManagementController::class)
                ->parameters(['staff' => 'user'])
                ->except(['show', 'update']);
            Route::put('staff/{user}/permissions', [App\Http\Controllers\Vendor\StaffManagementController::class, 'updatePermissions'])->name('staff.update-permissions');
        });
        
        Route::get('/expenses', fn() => inertia('vendor/Expenses'))->name('expenses')->middleware('permission:view-expenses');
        Route::get('/analytics', fn() => inertia('vendor/Analytics'))->name('analytics')->middleware('permission:view-analytics');
    });

    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
    Route::post('/admin/categories', [CategoryController::class, 'store']);
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update']);
});
