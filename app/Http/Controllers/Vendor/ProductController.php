<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\InventoryDeletionLog;
use App\Models\Listing;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Get product image URL - tries S3 first, then local storage fallback.
     */
    private function getProductImageUrl($product): ?string
    {
        if (! $product->image_path) {
            return null;
        }

        if (str_starts_with($product->image_path, 'http')) {
            return $product->image_path;
        }

        // Try S3 first if configured
        if (config('filesystems.default') === 's3' || config('filesystems.cloud') === 's3') {
            try {
                if (Storage::disk('s3')->exists($product->image_path)) {
                    return Storage::disk('s3')->url($product->image_path);
                }
            } catch (\Throwable $e) {
                // S3 not reachable — fall through to local
            }
        }

        // Fallback to local public disk
        if (Storage::disk('public')->exists($product->image_path)) {
            return Storage::disk('public')->url($product->image_path);
        }

        // Last resort — asset helper
        return asset('storage/'.$product->image_path);
    }

    public function index(Request $request)
    {
        $search = $request->query('search', '');

        $query = Product::with('listings')->latest();

        // Apply search filter if provided
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();

        $products = $query->paginate(15)
            ->through(function ($product) {
                // Fetch category name from central database
                $category = DB::connection('mysql')
                    ->table('categories')
                    ->where('id', $product->category_id)
                    ->first();

                return [
                    'id' => $product->id,
                    'product_name' => $product->name,
                    'description' => $product->description,
                    'category_id' => $product->category_id,
                    'category_name' => $category?->name ?? null,
                    'listing_ids' => $product->listings->pluck('id')->values(),
                    'listing_names' => $product->listings->pluck('name')->values(),
                    'barcode' => $product->barcode,
                    'price' => $product->price,
                    'stock' => $product->stock,
                    'image_url' => $this->getProductImageUrl($product),
                    'is_active' => $product->is_active,
                    'created_at' => $product->created_at,
                    'total_reviews' => $product->total_reviews ?? 0,
                    'average_rating' => $product->average_rating ?? 0,
                ];
            });

        // Fetch ALL categories from central database (including subcategories)
        $allCategories = DB::connection('mysql')
            ->table('categories')
            ->orderBy('parent_id')
            ->orderBy('name')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'category_name' => $cat->name,
                    'slug' => $cat->slug ?? '',
                    'description' => $cat->description ?? null,
                    'color' => $cat->color ?? '#000000',
                    'parent_id' => $cat->parent_id,
                    'children' => [],
                ];
            })
            ->toArray();

        // Build the category tree
        $categories = $this->buildCategoryTree($allCategories);

        $listings = Listing::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'is_active'])
            ->map(fn ($listing) => [
                'id' => $listing->id,
                'name' => $listing->name,
                'description' => $listing->description,
                'is_active' => (bool) $listing->is_active,
            ]);

        return inertia('vendor/Products', [
            'products' => $products,
            'categories' => $categories,
            'listings' => $listings,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'search' => $search,
        ]);
    }

    /**
     * Build a nested category tree from flat categories
     */
    private function buildCategoryTree(array $categories): array
    {
        $categoryMap = [];
        $tree = [];

        foreach ($categories as $category) {
            $categoryMap[$category['id']] = $category;
        }

        foreach ($categoryMap as $id => &$category) {
            if ($category['parent_id'] !== null && isset($categoryMap[$category['parent_id']])) {
                $categoryMap[$category['parent_id']]['children'][] = &$category;
            } else {
                $tree[] = &$category;
            }
        }

        return $tree;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'listing_ids' => 'nullable|array',
            'listing_ids.*' => 'integer|exists:listings,id',
            'barcode' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['name'] = $validated['product_name'];
        unset($validated['product_name']);

        $validated['category_id'] = isset($validated['category_id']) && $validated['category_id'] !== ''
            ? (int) $validated['category_id']
            : null;

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $path = $request->file('image')
                ->store('products', ['disk' => 's3']);

            if (! $path) {
                return back()->withErrors(['image' => 'Unable to upload image to S3.']);
            }

            $validated['image_path'] = $path;
        }

        $product = Product::create($validated);

        $product->listings()->sync($request->input('listing_ids', []));

        return back()->with('success', 'Product created successfully!');
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|integer',
            'listing_ids' => 'nullable|array',
            'listing_ids.*' => 'integer|exists:listings,id',
            'barcode' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $validated['name'] = $validated['product_name'];
        unset($validated['product_name']);

        $validated['category_id'] = isset($validated['category_id']) && $validated['category_id'] !== ''
            ? (int) $validated['category_id']
            : null;

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            // Delete old image from S3 if exists
            if ($product->image_path) {
                Storage::disk('s3')->delete($product->image_path);
            }

            $path = $request->file('image')->store('products', ['disk' => 's3']);

            if (! $path) {
                return back()->withErrors(['image' => 'Unable to upload image to S3.']);
            }

            $validated['image_path'] = $path;
        }

        $product->update($validated);

        $product->listings()->sync($request->input('listing_ids', []));

        return back()->with('success', 'Product updated successfully!');
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
            'notes' => "Deleted from product catalog by {$deletedBy}. Stock={$product->stock}, unit_price={$product->price}, total_value={$totalValue}",
        ]);

        Log::info('Product deleted', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'stock' => $product->stock,
            'unit_price' => $product->price,
            'deleted_by' => $deletedBy,
            'deleted_by_id' => $user?->id,
        ]);

        // Delete product image from S3 if exists
        if ($product->image_path) {
            Storage::disk('s3')->delete($product->image_path);
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully!');
    }
}