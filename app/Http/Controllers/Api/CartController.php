<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\CustomerOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CartController extends Controller
{
    public function index()
    {
        $carts = Cart::where('user_id', auth()->id())
            ->get()
            ->groupBy('store_id')
            ->map(function ($items, $storeId) {
                $tenant = Tenant::find($storeId);

                $cartItems = $items->map(function ($item) use ($tenant) {
                    $product = null;
                    if ($tenant) {
                        $product = $tenant->run(function () use ($item) {
                            return \App\Models\Product::find($item->product_id);
                        });
                    }

                    // Product was deleted from tenant DB — skip it
                    if (!$product) {
                        return null;
                    }

                    return [
                        'id'         => $item->id,
                        'product_id' => $item->product_id,
                        'quantity'   => $item->quantity,
                        'product'    => [
                            'id'         => $product->id,
                            'name'       => $product->name,
                            'price'      => (float) $product->price,
                            'image_path' => $product->image_path,
                            'stock'      => $product->stock,
                            'is_active'  => $product->is_active,
                        ],
                    ];
                })->filter()->values(); // remove nulls (deleted products)

                // Clean up orphaned cart rows whose product no longer exists
                $validProductIds = $cartItems->pluck('product_id')->toArray();
                Cart::where('user_id', auth()->id())
                    ->where('store_id', $storeId)
                    ->whereNotIn('product_id', $validProductIds)
                    ->delete();

                return [
                    'store_id'   => $storeId,
                    'store_name' => $tenant?->name ?? $storeId,
                    'store_logo' => $tenant?->logo ?? null,
                    'items'      => $cartItems,
                ];
            })
            ->filter(fn($group) => $group['items']->count() > 0) // drop empty stores
            ->values();

        return response()->json(['data' => $carts]);
    }

    public function add(Request $request)
    {
        $validated = $request->validate([
            'store_id'   => 'required|string',
            'product_id' => 'required|integer',
            'quantity'   => 'required|integer|min:1',
        ]);

        $cart = Cart::where([
            'user_id'    => auth()->id(),
            'store_id'   => $validated['store_id'],
            'product_id' => $validated['product_id'],
        ])->first();

        if ($cart) {
            // Increment existing quantity
            $cart->increment('quantity', $validated['quantity']);
        } else {
            $cart = Cart::create([
                'user_id'    => auth()->id(),
                'store_id'   => $validated['store_id'],
                'product_id' => $validated['product_id'],
                'quantity'   => $validated['quantity'],
            ]);
        }

        return response()->json([
            'message' => 'Product added to cart',
            'data'    => $cart,
        ]);
    }


    public function update(Request $request, Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart->update($validated);

        return response()->json([
            'message' => 'Cart updated',
            'data'    => $cart,
        ]);
    }

    public function destroy(Cart $cart)
    {
        if ($cart->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    public function destroyMany(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ]);

        Cart::where('user_id', auth()->id())
            ->whereIn('id', $validated['ids'])
            ->delete();

        return response()->json(['message' => 'Items removed from cart']);
    }

    public function clear()
    {
        Cart::where('user_id', auth()->id())->delete();

        return response()->json(['message' => 'Cart cleared']);
    }

    public function preorder(Request $request)
    {
        $validated = $request->validate([
            'cart_ids'   => 'required|array|min:1',
            'cart_ids.*' => 'integer',
        ]);

        $user = auth()->user();

        $cartItems = Cart::where('user_id', $user->id)
            ->whereIn('id', $validated['cart_ids'])
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json([
                'message' => 'No valid cart items selected.',
            ], 422);
        }

        $groupedByStore = $cartItems->groupBy('store_id');

        $createdOrders = [];
        $orderedCartIds = [];

        DB::beginTransaction();

        try {
            foreach ($groupedByStore as $storeId => $items) {
                $tenant = Tenant::find($storeId);

                if (!$tenant) {
                    throw new \Exception("Store/tenant [{$storeId}] not found.");
                }

                $tenantOrderData = $tenant->run(function () use ($items, $user, $storeId) {
                    $lineItems = [];
                    $grandTotal = 0;

                    foreach ($items as $cartItem) {
                        $product = \App\Models\Product::find($cartItem->product_id);

                        if (!$product) {
                            throw new \Exception("Product {$cartItem->product_id} not found in store {$storeId}.");
                        }

                        if (!$product->is_active) {
                            throw new \Exception("Product {$product->name} is inactive.");
                        }

                        if ($product->stock < $cartItem->quantity) {
                            throw new \Exception("Insufficient stock for {$product->name}.");
                        }

                        $lineTotal = $product->price * $cartItem->quantity;
                        $grandTotal += $lineTotal;

                        $lineItems[] = [
                            'product_id'   => $product->id,
                            'product_name' => $product->name,
                            'price'        => $product->price,
                            'quantity'     => $cartItem->quantity,
                            'total'        => $lineTotal,
                        ];
                    }

                    $orderNumber = 'PO-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));

                    $order = \App\Models\Order::create([
                        'user_id'      => $user->id, // central customer id
                        'order_number' => $orderNumber,
                        'total'        => $grandTotal,
                        'status'       => 'pending',
                        'placed_at'    => now(),
                    ]);

                    foreach ($lineItems as $line) {
                        \App\Models\OrderItem::create([
                            'order_id'     => $order->id,
                            'product_id'   => $line['product_id'],
                            'product_name' => $line['product_name'],
                            'price'        => $line['price'],
                            'quantity'     => $line['quantity'],
                            'total'        => $line['total'],
                        ]);

                        // optional stock deduction now
                        // \App\Models\Product::where('id', $line['product_id'])->decrement('stock', $line['quantity']);
                    }

                    return [
                        'tenant_id'    => $storeId,
                        'order_id'     => $order->id,
                        'order_number' => $order->order_number,
                        'total'        => $order->total,
                        'status'       => $order->status,
                        'ordered_at'   => $order->placed_at,
                    ];
                });

                CustomerOrder::create([
                    'user_id'      => $user->id,
                    'tenant_id'    => $tenantOrderData['tenant_id'],
                    'order_id'     => $tenantOrderData['order_id'],
                    'order_number' => $tenantOrderData['order_number'],
                    'total'        => $tenantOrderData['total'],
                    'status'       => $tenantOrderData['status'],
                    'ordered_at'   => $tenantOrderData['ordered_at'],
                ]);

                $createdOrders[] = $tenantOrderData;
                $orderedCartIds = array_merge($orderedCartIds, $items->pluck('id')->toArray());
            }

            Cart::where('user_id', $user->id)
                ->whereIn('id', $orderedCartIds)
                ->delete();

            DB::commit();

            return response()->json([
                'message' => 'Preorder placed successfully.',
                'orders'  => $createdOrders,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
