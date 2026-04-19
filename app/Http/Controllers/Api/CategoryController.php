<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        // Fetch ALL categories from central DB
        $allCategories = DB::connection('central')
            ->table('categories')
            ->orderBy('name')
            ->get();

        // Build children map
        $children = [];
        foreach ($allCategories as $cat) {
            if (!is_null($cat->parent_id)) {
                $children[$cat->parent_id][] = [
                    'id'        => $cat->id,
                    'name'      => $cat->name,
                    'slug'      => $cat->slug,
                    'color'     => $cat->color ?? '#6366f1',
                    'parent_id' => $cat->parent_id,
                    'children'  => [],
                ];
            }
        }

        // Build root categories with children attached
        $data = $allCategories
            ->filter(fn($cat) => is_null($cat->parent_id))
            ->map(fn($cat) => [
                'id'        => $cat->id,
                'name'      => $cat->name,
                'slug'      => $cat->slug,
                'color'     => $cat->color ?? '#6366f1',
                'parent_id' => null,
                'children'  => $children[$cat->id] ?? [],
            ])
            ->values();

        return response()->json(['data' => $data]);
    }
}