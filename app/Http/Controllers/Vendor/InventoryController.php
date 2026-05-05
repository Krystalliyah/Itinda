<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\InventoryDeletionLog;
use App\Models\InventoryMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class InventoryController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'product_name' => $p->name,
                'category' => $p->category?->name ?? 'Uncategorized',
                'barcode' => $p->barcode ?? $p->sku ?? '—',
                'stock_level' => $p->stock,
                'unit_price' => (float) $p->price,
                'reorder_level' => 10, // default; extend schema if needed
                'is_available' => (bool) $p->is_active,
            ];
        });

        $stats = [
            'total' => $products->count(),
            'outOfStock' => $products->where('stock_level', 0)->count(),
            'lowStock' => $products->filter(fn ($p) => $p['stock_level'] > 0 && $p['stock_level'] <= $p['reorder_level'])->count(),
            'totalValue' => (float) $products->sum(fn ($p) => $p['stock_level'] * $p['unit_price']),
        ];

        return inertia('vendor/Inventory', [
            'inventoryItems' => $products->values(),
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'stock_level' => 'required|integer|min:0',
            'unit_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_available' => 'required|boolean',
        ]);

        $oldStock = $product->stock;
        $newStock = $validated['stock_level'];

        $product->update([
            'stock' => $newStock,
            'price' => $validated['unit_price'],
            'is_active' => $validated['is_available'],
        ]);

        // Record inventory movement if stock changed
        if ($oldStock !== $newStock) {
            $diff = $newStock - $oldStock;
            InventoryMovement::create([
                'product_id' => $product->id,
                'quantity' => abs($diff),
                'type' => $diff > 0 ? 'in' : 'out',
                'reference' => 'manual-adjustment',
                'notes' => "Stock adjusted from {$oldStock} to {$newStock}",
            ]);
        }

        return back()->with('success', 'Inventory updated.');
    }

    public function toggleAvailability(Product $product)
    {
        $product->is_active = ! $product->is_active;
        $product->save();

        return back()->with('_toggleResult', [
            'is_available' => (bool) $product->is_active,
        ]);
    }

    public function destroy(Product $product)
    {
        $user = auth()->user();
        $deletedBy = $user?->login_id ?? $user?->email ?? 'Unknown user';
        $totalValue = (float) $product->stock * (float) $product->price;

        InventoryDeletionLog::create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock_level' => $product->stock,
            'unit_price' => $product->price,
            'total_value' => $totalValue,
            'deleted_by_id' => $user?->id,
            'deleted_by' => $deletedBy,
            'notes' => "Deleted from inventory by {$deletedBy}. Stock={$product->stock}, unit_price={$product->price}, total_value={$totalValue}",
        ]);

        Log::info('Inventory item deleted', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock' => $product->stock,
            'unit_price' => $product->price,
            'deleted_by' => $deletedBy,
            'deleted_by_id' => $user?->id,
        ]);

        if ($product->image_path) {
            Storage::disk('s3')->delete($product->image_path);
        }

        $product->delete();

        return back()->with('success', 'Inventory item deleted successfully.');
    }
}
