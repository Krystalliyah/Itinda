<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use Inertia\Inertia;
use App\Http\Controllers\Vendor\AnalyticsController;
use App\Http\Controllers\Vendor\ExpenseController;


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
use App\Http\Controllers\Vendor\InventoryController;
use App\Http\Controllers\Vendor\OrderController;
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
            $tenant = tenant();

            $totalProducts  = \App\Models\Product::count();
            $activeOrders   = \App\Models\Order::whereIn('status', ['pending','confirmed','preparing','ready'])->count();
            $totalSales     = \App\Models\Order::where('status', 'completed')->sum('total');
            $uniqueCustomers= \App\Models\Order::distinct('user_id')->count('user_id');

            // Recent Orders — last 5 by placed_at, any status
            $recentOrders = \App\Models\Order::with('items')
                ->latest('placed_at')
                ->take(5)
                ->get()
                ->map(fn($o) => [
                    'id'            => $o->id,
                    'order_number'  => $o->order_number,
                    'status'        => $o->status,
                    'total'         => (float) $o->total,
                    'placed_at'     => $o->placed_at?->toISOString(),
                    'items_summary' => $o->items->take(2)->map(fn($i) => "{$i->quantity}× {$i->product_name}")->join(', ')
                        . ($o->items->count() > 2 ? ' +' . ($o->items->count() - 2) . ' more' : ''),
                    'customer'      => ['name' => 'Customer #' . $o->user_id],
                ]);

            // Low Stock — products at or below reorder threshold (10)
            $lowStockItems = \App\Models\Product::where('stock', '<=', 10)
                ->where('is_active', true)
                ->orderBy('stock')
                ->take(5)
                ->get()
                ->map(fn($p) => [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'sku'          => $p->sku ?? $p->barcode ?? '—',
                    'stock'        => $p->stock,
                    'stockPercent' => min(100, ($p->stock / 10) * 100),
                    'level'        => $p->stock === 0 ? 'Critical' : 'Low',
                    'emoji'        => '📦',
                ]);

            return inertia('vendor/Dashboard', [
                'tenantInfo' => [
                    'id'       => $tenant->id,
                    'name'     => $tenant->name,
                    'database' => 'tenant_' . $tenant->id,
                ],
                'stats' => [
                    'orders'     => $activeOrders,
                    'products'   => $totalProducts,
                    'totalSales' => (float) $totalSales,
                    'customers'  => $uniqueCustomers,
                ],
                'recentOrders'  => $recentOrders,
                'lowStockItems' => $lowStockItems,
            ]);
        })->name('dashboard');
        Route::get('/profile', fn() => inertia('vendor/Profile'))->name('profile');
    });

    Route::middleware(['auth', 'verified', 'role:vendor|staff', 'vendor.is_approved'])->prefix('vendor')->name('vendor.')->group(function () {
        // Products management
        Route::middleware('permission:manage-products')->group(function() {
            Route::apiResource('products', ProductController::class)->except(['show']);
        });
        
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory')->middleware('permission:manage-inventory');
        Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update')->middleware('permission:manage-inventory');
        Route::patch('/inventory/{product}/toggle', [InventoryController::class, 'toggleAvailability'])->name('inventory.toggle')->middleware('permission:manage-inventory');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders')->middleware('permission:manage-orders');
        Route::post('/orders/{order}/advance', [OrderController::class, 'advance'])->name('orders.advance')->middleware('permission:manage-orders');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel')->middleware('permission:manage-orders');

        // Management routes
        Route::get('/store-settings', function() {
            $tenant = tenant();
            $domain = $tenant->domains->first()?->domain ?? null;
            return inertia('vendor/StoreSettings', [
                'tenantInfo' => [
                    'id'             => $tenant->id,
                    'name'           => $tenant->name,
                    'email'          => $tenant->email,
                    'phone'          => $tenant->phone ?? null,
                    'address'        => $tenant->address ?? null,
                    'slug'           => $tenant->id,
                    'domain'         => $domain,
                    'description'    => $tenant->description ?? null,
                    'operating_hours'=> $tenant->operating_hours ?? null,
                ],
            ]);
        })->name('store.settings')->middleware('permission:manage-store-settings');
        
        // Staff Management
        Route::middleware('permission:manage-staff')->group(function() {
            Route::apiResource('staff', App\Http\Controllers\Vendor\StaffManagementController::class)
                ->parameters(['staff' => 'user'])
                ->except(['show', 'update']);
            Route::put('staff/{user}/permissions', [App\Http\Controllers\Vendor\StaffManagementController::class, 'updatePermissions'])->name('staff.update-permissions');
        });
        
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses')->middleware('permission:view-expenses');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store')->middleware('permission:view-expenses');
        Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update')->middleware('permission:view-expenses');
        Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy')->middleware('permission:view-expenses');
        Route::get('/analytics', AnalyticsController::class)
            ->name('analytics')
            ->middleware('permission:view-analytics');
    });

    Route::middleware(['auth', 'verified', 'role:vendor|staff'])->group(function () {
        Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::post('/admin/categories', [CategoryController::class, 'store']);
        Route::put('/admin/categories/{category}', [CategoryController::class, 'update']);
    });

    // API Routes for vendors
    Route::get('/api/categories', [App\Http\Controllers\Api\CategoryController::class, 'index']);
});
