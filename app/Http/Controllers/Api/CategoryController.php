<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        // Get categories from central database
        $categories = DB::connection('mysql')
            ->table('categories')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get()
            ->map(function ($cat) {
                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'color' => $cat->color ?? '#6366f1',
                ];
            });

        return response()->json([
            'data' => $categories
        ]);
    }
}
