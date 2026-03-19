<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerOrderResource;
use App\Models\CustomerOrder;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $customerOrders = CustomerOrder::query()
            ->where('user_id', $user->id)
            ->latest('ordered_at')
            ->get();

        $orders = $customerOrders->map(function ($customerOrder) {
            return $this->transformOrder($customerOrder);
        });

        return CustomerOrderResource::collection($orders);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();

        $customerOrder = CustomerOrder::query()
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $order = $this->transformOrder($customerOrder);

        return new CustomerOrderResource($order);
    }

    private function transformOrder(CustomerOrder $customerOrder): array
    {
        $tenant = Tenant::query()->find($customerOrder->tenant_id);
        $storeData = $tenant?->data ?? [];

        $items = [];

        if ($tenant) {
            $items = $this->getTenantOrderItems($tenant, $customerOrder->order_id);
        }

        return [
            'id' => $customerOrder->id,
            'customer_id' => $customerOrder->user_id,
            'store_id' => $customerOrder->tenant_id,
            'order_number' => $customerOrder->order_number ?? 'NA',
            'status' => $customerOrder->status ?? 'pending',
            'total_amount' => (float) ($customerOrder->total ?? 0),
            'pickup_date' => null,
            'notes' => null,
            'created_at' => optional($customerOrder->created_at)?->toISOString(),
            'updated_at' => optional($customerOrder->updated_at)?->toISOString(),
            'store' => [
                'id' => $tenant?->id ?? 'NA',
                'name' => $tenant?->name ?? 'NA',
                'logo_url' => $storeData['logo'] ?? null,
            ],
            'items' => $items,
        ];
    }

    private function getTenantOrderItems(Tenant $tenant, int $orderId): array
    {
        $items = [];

        $tenant->run(function () use (&$items, $orderId) {
            $orderItems = DB::table('order_items')
                ->where('order_id', $orderId)
                ->get();

            $productIds = $orderItems->pluck('product_id')->filter()->unique()->values();

            $products = collect();

            if ($productIds->isNotEmpty()) {
                $products = DB::table('products')
                    ->whereIn('id', $productIds)
                    ->get()
                    ->keyBy('id');
            }

            $items = $orderItems->map(function ($item) use ($products) {
                $product = $products->get($item->product_id);

                return [
                    'id' => $item->id,
                    'inventory_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'product' => [
                        'id' => $item->product_id,
                        'name' => $item->product_name ?? $product->name ?? 'NA',
                        'description' => $product->description ?? 'NA',
                        'price' => (float) ($item->price ?? $product->price ?? 0),
                        'image_url' => $product->image_path ?? null,
                    ],
                    'created_at' => $item->created_at,
                ];
            })->values()->toArray();
        });

        return $items;
    }
}