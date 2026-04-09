<?php

namespace App\Http\Controllers\Vendor;

use App\Events\OrderStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\Order;

class OrderController extends Controller
{
    private const VALID_STATUSES = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'];

    private const STATUS_MAP = [
        'pending' => 'pending',
        'confirmed' => 'pending',
        'preparing' => 'preparing',
        'ready' => 'ready_for_pickup',
        'completed' => 'picked_up',
        'cancelled' => 'cancelled',
    ];

    public function index()
    {
        $orders = Order::with('items')
            ->latest('placed_at')
            ->get()
            ->map(fn ($o) => $this->formatOrder($o));

        $stats = [
            'pending' => $orders->where('status', 'pending')->count(),
            'active' => $orders->whereIn('status', ['confirmed', 'preparing', 'ready'])->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'revenue' => $orders->where('status', 'completed')->sum('total_amount'),
        ];

        return inertia('vendor/Orders', [
            'orders' => $orders->values(),
            'stats' => $stats,
        ]);
    }

    public function advance(Order $order)
    {
        $next = $this->nextStatus($order->status);

        if (! $next) {
            return back()->with('error', 'Order cannot be advanced further.');
        }

        $order->update(['status' => $next]);
        $this->syncCustomerOrder($order);

        // Dispatch real-time event
        event(new OrderStatusUpdated($order));

        return back()->with('success', "Order marked as {$next}.");
    }

    public function cancel(Order $order)
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel a completed or already cancelled order.');
        }

        $order->update(['status' => 'cancelled']);
        $this->syncCustomerOrder($order);

        // Dispatch real-time event
        event(new OrderStatusUpdated($order));

        return back()->with('success', 'Order cancelled.');
    }

    public function export()
    {
        $orders = Order::with('items')->latest('placed_at')->get();
        $filename = 'orders-export-'.now()->format('Ymd-His').'.csv';

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Order #',
                'Placed At',
                'Status',
                'Total',
                'Paid',
                'Product',
                'Quantity',
                'Unit Price',
            ]);

            foreach ($orders as $order) {
                foreach ($order->items as $item) {
                    fputcsv($handle, [
                        $order->order_number,
                        $order->placed_at?->toDateTimeString(),
                        $order->status,
                        number_format($order->total, 2, '.', ''),
                        $order->status === 'completed' ? 'Yes' : 'No',
                        $item->product_name,
                        $item->quantity,
                        number_format($item->price, 2, '.', ''),
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function syncCustomerOrder(Order $order): void
    {
        $tenantId = tenant('id');
        $customerStatus = self::STATUS_MAP[$order->status] ?? $order->status;

        CustomerOrder::on('central')
            ->where('tenant_id', $tenantId)
            ->where('order_id', $order->id)
            ->update(['status' => $customerStatus]);
    }

    private function nextStatus(string $current): ?string
    {
        $flow = ['pending' => 'confirmed', 'confirmed' => 'preparing', 'preparing' => 'ready', 'ready' => 'completed'];

        return $flow[$current] ?? null;
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
            'total_amount' => (float) $order->total,
            'is_paid' => $order->status === 'completed',
            'placed_at' => $order->placed_at?->toISOString(),
            'created_at' => $order->created_at?->toISOString(),
            'items' => $order->items->map(fn ($i) => [
                'product_name' => $i->product_name,
                'quantity' => $i->quantity,
                'unit_price' => (float) $i->price,
            ])->toArray(),
        ];
    }
}
