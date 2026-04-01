<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\CustomerOrder;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = CustomerOrder::where('user_id', $request->user()->id)
            ->latest('ordered_at')
            ->get()
            ->map(fn($o) => $this->transformOrder($o));

        return response()->json(['data' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $order = CustomerOrder::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        return response()->json(['data' => $this->transformOrder($order)]);
    }

    public function cancel(Request $request, $id)
    {
        $order = CustomerOrder::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->firstOrFail();

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Only pending orders can be cancelled.'], 422);
        }

        $order->update(['status' => 'cancelled']);

        // Sync cancellation to the tenant order
        $tenant = Tenant::find($order->tenant_id);
        if ($tenant) {
            $tenant->run(function () use ($order) {
                \App\Models\Order::where('id', $order->order_id)->update(['status' => 'cancelled']);
            });
        }

        return response()->json(['message' => 'Order cancelled.']);
    }

    private function transformOrder(CustomerOrder $customerOrder): array
    {
        $tenant = Tenant::find($customerOrder->tenant_id);

        $items = $tenant ? $this->getTenantOrderItems($tenant, $customerOrder->order_id) : [];

        return [
            'id'           => $customerOrder->id,
            'customer_id'  => $customerOrder->user_id,
            'store_id'     => $customerOrder->tenant_id,
            'order_number' => $customerOrder->order_number ?? 'N/A',
            'status'       => $customerOrder->status ?? 'pending',
            'total_amount' => (float) $customerOrder->total,
            'pickup_date'  => null,
            'notes'        => null,
            'created_at'   => $customerOrder->created_at?->toISOString(),
            'updated_at'   => $customerOrder->updated_at?->toISOString(),
            'store'        => [
                'id'       => $tenant?->id ?? 'N/A',
                'name'     => $tenant?->name ?? 'Unknown Store',
                'logo_url' => $tenant?->data['logo'] ?? null,
            ],
            'items' => $items,
        ];
    }

    private function getTenantOrderItems(Tenant $tenant, int $orderId): array
    {
        $items = [];

        $tenant->run(function () use (&$items, $orderId) {
            $orderItems = DB::table('order_items')->where('order_id', $orderId)->get();
            $products   = DB::table('products')
                ->whereIn('id', $orderItems->pluck('product_id')->filter()->unique())
                ->get()->keyBy('id');

            $items = $orderItems->map(function ($item) use ($products) {
                $product = $products->get($item->product_id);
                return [
                    'id'           => $item->id,
                    'inventory_id' => $item->product_id,
                    'quantity'     => $item->quantity,
                    'created_at'   => $item->created_at,
                    'product'      => [
                        'id'          => $item->product_id,
                        'name'        => $item->product_name ?? $product?->name ?? 'N/A',
                        'description' => $product?->description ?? '',
                        'price'       => (float) ($item->price ?? $product?->price ?? 0),
                        'image_url'   => $product?->image_path
                            ? Storage::disk('s3')->url($product->image_path)
                            : null,
                    ],
                ];
            })->values()->toArray();
        });

        return $items;
    }
}
