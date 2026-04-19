<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Vendor\AnalyticsController;
use App\Http\Controllers\Vendor\ExpenseController;
use App\Http\Controllers\Vendor\InventoryController;
use App\Http\Controllers\Vendor\ListingController;
use App\Http\Controllers\Vendor\OrderController;
use App\Http\Controllers\Vendor\ProductController;
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

use App\Http\Controllers\Vendor\ProductReviewController;
use App\Http\Controllers\Vendor\ProfileController;
use App\Http\Controllers\Vendor\StaffManagementController;
use App\Http\Controllers\Vendor\StoreSettingsController;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

Route::middleware([
    InitializeTenancyByDomain::class,
    'web',
    PreventAccessFromCentralDomains::class,
    'consume-tenant-auth-token',
])->group(function () {
    // =========================================================================
    // PUBLIC ROUTES (No Authentication Required)
    // =========================================================================

    // Tenant root: guests go to local login, authenticated users to dashboard
    Route::get('/', function () {
        if (! auth()->check()) {
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

    // =========================================================================
    // EMAIL VERIFICATION ROUTES (Authenticated but NOT Verified)
    // =========================================================================
    // These routes are accessible to authenticated users who haven't verified their email yet

    Route::middleware(['auth'])->group(function () {
        // Email verification notice page - shown to users who need to verify
        Route::get('/email/verify', function () {
            return Inertia::render('auth/VerifyEmail');
        })->name('verification.notice');

        // Email verification handler - the link users click in the email
        // This route uses 'signed' middleware to ensure the URL hasn't been tampered with
        Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
            ->middleware(['signed'])
            ->name('verification.verify');

        // Resend verification email - for users who didn't receive the email
        Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
            ->middleware(['throttle:6,1'])
            ->name('verification.send');
    });

    // =========================================================================
    // PROTECTED ROUTES (Require Authentication AND Email Verification)
    // =========================================================================

    require __DIR__.'/settings.php';

    // Basic vendor routes (accessible to any vendor account or staff)
    Route::middleware(['auth', 'verified', 'role:vendor|staff'])->prefix('vendor')->name('vendor.')->group(function () {
        Route::get('/dashboard', function () {
            $tenant = tenant();

            $totalProducts = Product::count();
            $activeOrders = Order::whereIn('status', ['pending', 'confirmed', 'preparing', 'ready'])->count();
            $totalSales = Order::where('status', 'completed')->sum('total');
            $uniqueCustomers = Order::distinct('user_id')->count('user_id');

            // Recent Orders — last 5 by placed_at, any status
            $recentOrders = Order::with('items')
                ->latest('placed_at')
                ->take(5)
                ->get()
                ->map(fn ($o) => [
                    'id' => $o->id,
                    'order_number' => $o->order_number,
                    'status' => $o->status,
                    'total' => (float) $o->total,
                    'placed_at' => $o->placed_at?->toISOString(),
                    'items_summary' => $o->items->take(2)->map(fn ($i) => "{$i->quantity}× {$i->product_name}")->join(', ')
                        .($o->items->count() > 2 ? ' +'.($o->items->count() - 2).' more' : ''),
                    'customer' => ['name' => 'Customer #'.$o->user_id],
                ]);

            // Low Stock — products at or below reorder threshold (10)
            $lowStockItems = Product::where('stock', '<=', 10)
                ->where('is_active', true)
                ->orderBy('stock')
                ->take(5)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'sku' => $p->sku ?? $p->barcode ?? '—',
                    'stock' => $p->stock,
                    'stockPercent' => min(100, ($p->stock / 10) * 100),
                    'level' => $p->stock === 0 ? 'Critical' : 'Low',
                    'emoji' => '📦',
                ]);

            return inertia('vendor/Dashboard', [
                'tenantInfo' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'database' => 'tenant_'.$tenant->id,
                ],
                'stats' => [
                    'orders' => $activeOrders,
                    'products' => $totalProducts,
                    'totalSales' => (float) $totalSales,
                    'customers' => $uniqueCustomers,
                ],
                'recentOrders' => $recentOrders,
                'lowStockItems' => $lowStockItems,
            ]);
        })->name('dashboard');

        Route::get('/profile', [ProfileController::class, 'show'])->name('profile');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Advanced vendor routes with additional approval check
    Route::middleware(['auth', 'verified', 'role:vendor|staff', 'vendor.is_approved'])->prefix('vendor')->name('vendor.')->group(function () {
        // Products management
        Route::middleware('permission:manage-products')->group(function () {
            Route::apiResource('products', ProductController::class)->except(['show']);
            Route::apiResource('listings', ListingController::class)->except(['show']);
        });

        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory')->middleware('permission:manage-inventory');
        Route::put('/inventory/{product}', [InventoryController::class, 'update'])->name('inventory.update')->middleware('permission:manage-inventory');
        Route::patch('/inventory/{product}/toggle', [InventoryController::class, 'toggleAvailability'])->name('inventory.toggle')->middleware('permission:manage-inventory');
        Route::delete('/inventory/{product}', [InventoryController::class, 'destroy'])->name('inventory.destroy')->middleware('permission:manage-inventory');

        Route::get('/orders', [OrderController::class, 'index'])->name('orders')->middleware('permission:manage-orders');
        Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export')->middleware('permission:manage-orders');
        Route::post('/orders/{order}/advance', [OrderController::class, 'advance'])->name('orders.advance')->middleware('permission:manage-orders');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel')->middleware('permission:manage-orders');

        // Management routes
        Route::get('/store-settings', [StoreSettingsController::class, 'show'])
            ->name('store.settings')
            ->middleware('permission:manage-store-settings');

        Route::put('/store-settings', [StoreSettingsController::class, 'update'])
            ->name('store.settings.update')
            ->middleware('permission:manage-store-settings');

        // Staff Management
        Route::middleware('permission:manage-staff')->group(function () {
            Route::apiResource('staff', StaffManagementController::class)
                ->parameters(['staff' => 'user'])
                ->except(['show', 'update']);
            Route::put('staff/{user}/permissions', [StaffManagementController::class, 'updatePermissions'])->name('staff.update-permissions');
        });

        // Expenses Management - Full CRUD with date range filtering
        Route::middleware('permission:view-expenses')->group(function () {
            Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses');
            Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
            Route::put('/expenses/{expense}', [ExpenseController::class, 'update'])->name('expenses.update');
            Route::delete('/expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
            Route::get('/expenses/inventory-data', [ExpenseController::class, 'getInventorySpendData'])->name('expenses.inventory-data');
        });

        // Analytics route - using the invokable controller
        Route::get('/analytics', AnalyticsController::class)
            ->name('analytics')
            ->middleware('permission:view-analytics');

        // =========================================================================
        // PRODUCT REVIEWS MANAGEMENT
        // =========================================================================
        Route::middleware('permission:manage-products')->prefix('products/{product}/reviews')->name('products.reviews.')->group(function () {
            Route::get('/', [ProductReviewController::class, 'index'])->name('index');
            Route::post('/{review}/approve', [ProductReviewController::class, 'approve'])->name('approve');
            Route::delete('/{review}', [ProductReviewController::class, 'destroy'])->name('destroy');
            Route::post('/{review}/feature', [ProductReviewController::class, 'feature'])->name('feature');
            Route::post('/{review}/respond', [ProductReviewController::class, 'respond'])->name('respond');
        });
    });

    // Category management routes
    Route::middleware(['auth', 'verified', 'role:vendor|staff'])->group(function () {
        Route::get('/admin/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
        Route::post('/admin/categories', [CategoryController::class, 'store']);
        Route::put('/admin/categories/{category}', [CategoryController::class, 'update']);
    });

    // API Routes for vendors
    Route::get('/api/categories', [App\Http\Controllers\Api\CategoryController::class, 'index']);
});