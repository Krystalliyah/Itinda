<?php

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Laravel\Fortify\Features;

// Pin the home route to the exact central domain
Route::domain(config('app.domain'))->group(function () {
    Route::get('/', function () {
        // Redirect authenticated users to their appropriate dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        $tenants = Tenant::where('is_approved', true)->get();

        $openStoresCollection = $tenants->filter(function ($tenant) {
            $hours = $tenant->operating_hours;

            if (empty($hours) || ! is_array($hours)) {
                return false;
            }

            $now = now();
            $currentDay = strtolower($now->format('l'));
            $schedule = $hours[$currentDay] ?? null;

            if (! $schedule || empty($schedule['is_open'])) {
                return false;
            }

            $open = $schedule['open_time'] ?? null;
            $close = $schedule['close_time'] ?? null;

            if (! $open || ! $close) {
                return false;
            }

            $current = $now->format('H:i');

            return $current >= $open && $current <= $close;
        });

        $openStoreCount = $openStoresCollection->count();

        $openStoresData = $openStoresCollection->take(3)->map(function ($t) {
            $products = [];
            try {
                $t->run(function () use (&$products) {
                    $products = Product::where('products.is_active', true)
                        ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
                        ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
                        ->select(
                            'products.id',
                            'products.name',
                            'products.price',
                            'products.image_path',
                            DB::raw('SUM(CASE WHEN orders.status = "completed" THEN order_items.quantity ELSE 0 END) as sold_count')
                        )
                        ->groupBy('products.id', 'products.name', 'products.price', 'products.image_path')
                        ->orderByDesc('sold_count')
                        ->orderByDesc('products.created_at')
                        ->take(4)
                        ->get()
                        ->map(function ($p) {
                            $imageUrl = null;
                            if ($p->image_path) {
                                if (str_starts_with($p->image_path, 'http')) {
                                    $imageUrl = $p->image_path;
                                } elseif (Storage::disk('public')->exists($p->image_path)) {
                                    $imageUrl = Storage::disk('public')->url($p->image_path);
                                } else {
                                    $imageUrl = asset('storage/'.$p->image_path);
                                }
                            }

                            return [
                                'id' => $p->id,
                                'name' => $p->name,
                                'price' => $p->price,
                                'image_url' => $imageUrl,
                            ];
                        })
                        ->toArray();
                });
            } catch (Exception $e) {
                // Ignore missing DBs in demo data
            }

            return [
                'id' => $t->id,
                'name' => $t->name ?? 'Unnamed Store',
                'address' => $t->address ?? 'No address provided',
                'phone' => $t->phone ?? '',
                'hours' => 'Open Today',
                'isOpen' => true,
                'logo' => $t->profile_photo_url,
                'cover' => $t->cover_photo_url,
                'products' => $products,
            ];
        })->values()->all();

        return Inertia::render('Welcome', [
            'canRegister' => Features::enabled(Features::registration()),
            'vendorCount' => $tenants->count(),
            'customerCount' => User::role('customer')->count(),
            'openStoreCount' => $openStoreCount,
            'openStores' => $openStoresData,
        ]);
    })->name('home');

    Route::middleware(['auth'])->get('/dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('customer')) {
            return redirect()->route('customer.dashboard');
        }

        if ($user->hasRole('vendor')) {
            return redirect()->route('vendor.dashboard');
        }

        abort(403, 'No role assigned.');
    })->name('dashboard');

    // Central domain routes (admin, customer, settings)
    require __DIR__.'/settings.php';
    require __DIR__.'/admin.php';
    require __DIR__.'/customer.php';

    // Vendor routes (can be on central or tenant domain)
    require __DIR__.'/vendor.php';
});
