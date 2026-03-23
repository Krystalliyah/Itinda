<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Tenant;
use Illuminate\Http\Request;

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
}
