<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    private const VALID_STATUSES = ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'];

    public function index()
    {
        $orders = Order::with('items')
            ->latest('placed_at')
            ->get()
            ->map(fn($o) => $this->formatOrder($o));

        $stats = [
            'pending'   => $orders->where('status', 'pending')->count(),
            'active'    => $orders->whereIn('status', ['confirmed', 'preparing', 'ready'])->count(),
            'completed' => $orders->where('status', 'completed')->count(),
            'revenue'   => $orders->where('status', 'completed')->sum('total_amount'),
        ];

        return inertia('vendor/Orders', [
            'orders' => $orders->values(),
            'stats'  => $stats,
        ]);
    }

    public function advance(Order $order)
    {
        $next = $this->nextStatus($order->status);

        if (!$next) {
            return back()->with('error', 'Order cannot be advanced further.');
        }

        $order->update(['status' => $next]);
        return back()->with('success', "Order marked as {$next}.");
    }

    public function cancel(Order $order)
    {
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel a completed or already cancelled order.');
        }

        $order->update(['status' => 'cancelled']);
        return back()->with('success', 'Order cancelled.');
    }

    private function nextStatus(string $current): ?string
    {
        $flow = ['pending' => 'confirmed', 'confirmed' => 'preparing', 'preparing' => 'ready', 'ready' => 'completed'];
        return $flow[$current] ?? null;
    }

    private function formatOrder(Order $order): array
    {
        return [
            'id'            => $order->id,
            'order_number'  => $order->order_number,
            'status'        => $order->status,
            'total_amount'  => (float) $order->total,
            'is_paid'       => $order->status === 'completed', // extend schema if payment tracking needed
            'placed_at'     => $order->placed_at?->toISOString(),
            'created_at'    => $order->created_at?->toISOString(),
            'items'         => $order->items->map(fn($i) => [
                'product_name' => $i->product_name,
                'quantity'     => $i->quantity,
                'unit_price'   => (float) $i->price,
            ])->toArray(),
        ];
    }
}
