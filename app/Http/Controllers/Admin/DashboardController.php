<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $vendorStats = [
            'pending' => Tenant::where('is_approved', false)->count(),
            'active' => Tenant::where('is_approved', true)->count(),
            'total' => Tenant::count(),
        ];

        $customerCount = User::role('customer')->count();

        return inertia('admin/Dashboard', [
            'vendorStats' => $vendorStats,
            'customerCount' => $customerCount,
        ]);
    }
}
