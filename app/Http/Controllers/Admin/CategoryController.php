<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
{
    $categories = Category::with(['children' => function ($q) {
                        $q->orderBy('name');
                    }])
                    ->parents()
                    ->orderBy('name')
                    ->get();
    
    // Aggregate product counts across all tenant databases.
    // Products live in tenant DBs (category_id FK), categories in central DB.
    $productCounts = []; // [category_id => count]

    foreach (Tenant::query()->where('is_approved', true)->get() as $tenant) {
        try {
            $tenant->run(function () use (&$productCounts) {
                $rows = DB::table('products')
                    ->selectRaw('category_id, COUNT(*) as cnt')
                    ->whereNotNull('category_id')
                    ->groupBy('category_id')
                    ->get();

                foreach ($rows as $row) {
                    $productCounts[$row->category_id] = ($productCounts[$row->category_id] ?? 0) + $row->cnt;
                }
            });
        } catch (\Exception $e) {
            \Log::warning("Could not read products from tenant {$tenant->id}: " . $e->getMessage());
        }
    }

    $formattedCategories = $categories->map(fn ($cat) => $this->formatCategory($cat, $productCounts));

    return inertia('admin/Categories', [
        'categories' => $formattedCategories,
    ]);
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')],
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
            'parent_id'   => ['nullable', 'integer', Rule::exists('categories', 'id')],
        ]);

        // If no color is provided and the category is a sub-category, set default color based on the parent category's color
        if (empty($validated['color']) && $validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            $validated['color'] = $parent?->color ?? '#6366f1'; // Default to a generic color
        }

        // Create the category (this can be a parent or a sub-category)
        $category = Category::create($validated);

        // Sync the category to tenants
        foreach (Tenant::all() as $tenant) {
            try {
                $tenant->run(function () use ($validated, $category) {
                    DB::table('categories')->updateOrCreate(
                        ['id' => $category->id],
                        array_merge($validated, ['created_at' => now(), 'updated_at' => now()])
                    );
                });
            } catch (\Exception $e) {
                \Log::error("Could not sync category {$category->id} to tenant {$tenant->id}: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
            'parent_id'   => ['nullable', 'integer', Rule::exists('categories', 'id')], // Handle parent_id for sub-categories
        ]);

        // Handle parent_id change for sub-categories
        if (isset($validated['parent_id']) && $validated['parent_id'] !== $category->parent_id) {
            $category->parent_id = $validated['parent_id']; // Update the parent_id if changed
        }

        $category->update($validated); // Update the category

        // Sync the updated category across all tenants
        foreach (Tenant::all() as $tenant) {
            try {
                $tenant->run(function () use ($category, $validated) {
                    DB::table('categories')
                        ->where('id', $category->id)
                        ->update(array_merge($validated, ['updated_at' => now()]));
                });
            } catch (\Exception $e) {
                \Log::error("Could not sync category update {$category->id} to tenant {$tenant->id}: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Format a category (and its children) for the frontend.
     *
     * @param  array<int,int>  $productCounts  [category_id => total across all tenants]
     */
    private function formatCategory(Category $cat, array $productCounts = [], bool $isChild = false): array
    {
        $data = [
            'id'            => $cat->id,
            'name'          => $cat->name,
            'slug'          => $cat->slug,
            'description'   => $cat->description ?? '',
            'color'         => $cat->color ?? '#6366f1',
            'parent_id'     => $cat->parent_id,
            'product_count' => $productCounts[$cat->id] ?? 0,
            'children'      => [],
        ];

        if (! $isChild && $cat->relationLoaded('children')) {
            $data['children'] = $cat->children
                ->map(fn ($child) => $this->formatCategory($child, $productCounts, true))
                ->values()
                ->all();
        }

        return $data;
    }
}