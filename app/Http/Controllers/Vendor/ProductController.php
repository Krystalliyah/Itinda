<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()
            ->paginate(15)
            ->through(function ($product) {
                // Fetch category name from central database
                $category = \Illuminate\Support\Facades\DB::connection('mysql')
                    ->table('categories')
                    ->where('id', $product->category_id)
                    ->first();
                
                return [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'description' => $product->description,
                    'category_name' => $category?->name ?? null,
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image_url' => $product->image_path ? asset('storage/' . $product->image_path) : null,
                    'is_active' => $product->is_active,
                    'created_at' => $product->created_at,
                ];
            });
        
        // Fetch categories from central database
        $categories = \Illuminate\Support\Facades\DB::connection('mysql')
            ->table('categories')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'category_name' => $cat->name,
                ];
            });
        
        return inertia('vendor/Products', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'barcode' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Map product_name to name for the model
        $validated['name'] = $validated['product_name'];
        unset($validated['product_name']);

        // Convert category_id to integer if it exists
        if (isset($validated['category_id']) && $validated['category_id'] !== '') {
            $validated['category_id'] = (int) $validated['category_id'];
        } else {
            $validated['category_id'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        Product::create($validated);

        return back()->with('success', 'Product created successfully!');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'barcode' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Map product_name to name for the model
        $validated['name'] = $validated['product_name'];
        unset($validated['product_name']);

        // Convert category_id to integer if it exists
        if (isset($validated['category_id']) && $validated['category_id'] !== '') {
            $validated['category_id'] = (int) $validated['category_id'];
        } else {
            $validated['category_id'] = null;
        }

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return back()->with('success', 'Product updated successfully!');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return back()->with('success', 'Product deleted successfully!');
    }
}
