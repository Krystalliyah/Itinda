<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Tenant;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CustomerPageController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        // Current active order — show until picked_up/completed so the customer
        // sees the final "Picked Up" state before it moves to recent orders
        $currentOrder = CustomerOrder::where('user_id', $user->id)
            ->whereNotIn('status', ['cancelled'])
            ->latest('ordered_at')
            ->first();

        $currentOrderData = null;
        if ($currentOrder) {
            $tenant = Tenant::find($currentOrder->tenant_id);
            $currentOrderData = [
                'id' => $currentOrder->id,
                'order_number' => $currentOrder->order_number ?? 'N/A',
                'store' => $tenant?->name ?? 'Unknown Store',
                'store_id' => $currentOrder->tenant_id,
                'status' => $currentOrder->status,
                'total' => (float) $currentOrder->total,
                'ordered_at' => optional($currentOrder->ordered_at)->toISOString(),
            ];
        }

        // Recent completed orders (last 5)
        $recentOrders = CustomerOrder::where('user_id', $user->id)
            ->whereIn('status', ['picked_up', 'completed'])
            ->latest('ordered_at')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $tenant = Tenant::find($order->tenant_id);

                return [
                    'id' => $order->id,
                    'order_number' => $order->order_number ?? 'N/A',
                    'store' => $tenant?->name ?? 'Unknown Store',
                    'store_id' => $order->tenant_id,
                    'status' => $order->status,
                    'total' => (float) $order->total,
                    'ordered_at' => optional($order->ordered_at)->toISOString(),
                ];
            });

        // Approved stores for recommendations
        $stores = Cache::remember('approved_stores_list', 60, function () {
            return Tenant::where('is_approved', true)
                ->with('domains')
                ->get()
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'domain' => $t->domains->first()?->domain,
                    'hours' => $t->operating_hours,
                    'logo' => $t->data['logo'] ?? null,
                    'isOpen' => $this->checkStoreIsOpen($t->operating_hours),
                ]);
        });

        return inertia('customer/Dashboard', [
            'currentOrder' => $currentOrderData,
            'recentOrders' => $recentOrders,
            'stores' => $stores,
        ]);
    }

    public function stores()
    {
        return inertia('customer/Stores');
    }

    public function showStore($id)
    {
        return inertia('customer/StoreDetails', [
            'storeId' => $id,
        ]);
    }

    public function products()
    {
        return inertia('customer/Products');
    }

    public function showProduct($storeId, $productId)
    {
        return inertia('customer/ProductDetail', [
            'storeId' => $storeId,
            'productId' => (int) $productId,
        ]);
    }

    public function orders()
    {
        return inertia('customer/Orders');
    }

    public function profile(Request $request)
    {
        return inertia('customer/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    public function cart()
    {
        return inertia('customer/Cart');
    }

    private function checkStoreIsOpen(?array $operatingHours): bool
    {
        if (empty($operatingHours) || ! is_array($operatingHours)) {
            return false;
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));
        $schedule = $operatingHours[$currentDay] ?? null;

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
    }
}
