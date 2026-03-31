<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = DB::connection('central')
            ->table('categories')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();

        $productCounts = Product::query()
            ->select('category_id', DB::raw('COUNT(*) as products_count'))
            ->whereNotNull('category_id')
            ->groupBy('category_id')
            ->pluck('products_count', 'category_id');

        $data = $categories->map(function ($cat) use ($productCounts) {
            return [
                'id' => $cat->id,
                'name' => $cat->name,
                'slug' => $cat->slug,
                'color' => $cat->color ?? '#6366f1',
                'products_count' => (int) ($productCounts[$cat->id] ?? 0),
            ];
        });

        return response()->json([
            'data' => $data
        ]);
    }
}