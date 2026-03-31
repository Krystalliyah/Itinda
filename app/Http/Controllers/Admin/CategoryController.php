<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * List all categories nested under their parents.
     * Runs inside the tenant context (tenant middleware handles this).
     */
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

        foreach (\App\Models\Tenant::where('is_approved', true)->get() as $tenant) {
            try {
                $tenant->run(function () use (&$productCounts) {
                    $rows = \Illuminate\Support\Facades\DB::table('products')
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

        $formatted = $categories->map(fn ($cat) => $this->formatCategory($cat, $productCounts));

        return inertia('admin/Categories', [
            'categories' => $formatted,
        ]);
    }

    /**
     * Store a new category (parent or child).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')],
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
            'parent_id'   => ['nullable', 'integer', Rule::exists('categories', 'id')],
        ]);

        // Children inherit parent color if none given
        if (empty($validated['color']) && $validated['parent_id']) {
            $parent = Category::find($validated['parent_id']);
            $validated['color'] = $parent?->color ?? '#6366f1';
        }

        $category = Category::create($validated);

        // Sync category to all tenant databases
        foreach (\App\Models\Tenant::all() as $tenant) {
            try {
                $tenant->run(function () use ($validated, $category) {
                    \Illuminate\Support\Facades\DB::table('categories')->updateOrCreate(
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


    /**
     * Update an existing category.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('categories', 'slug')->ignore($category->id)],
            'description' => 'nullable|string|max:1000',
            'color'       => 'nullable|string|max:20',
        ]);

        $category->update($validated);

        // Sync update to all tenant databases
        foreach (\App\Models\Tenant::all() as $tenant) {
            try {
                $tenant->run(function () use ($category, $validated) {
                    \Illuminate\Support\Facades\DB::table('categories')
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