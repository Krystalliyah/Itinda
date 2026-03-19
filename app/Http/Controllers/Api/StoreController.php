<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StoreResource;
use App\Models\Tenant;

class StoreController extends Controller
{
    public function index()
    {
        $stores = Tenant::query()
            ->where('is_approved', 1)
            ->latest()
            ->get();

        return StoreResource::collection($stores);
    }

    public function show($id)
    {
        $store = Tenant::query()
            ->where('is_approved', 1)
            ->findOrFail($id);

        return new StoreResource($store);
    }
}