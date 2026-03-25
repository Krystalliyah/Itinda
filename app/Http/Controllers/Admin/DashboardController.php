<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PlatformHealthService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $vendorStats = [
            'pending' => Tenant::where('is_approved', false)->count(),
            'active'  => Tenant::where('is_approved', true)->count(),
            'total'   => Tenant::count(),
        ];

        $customerCount     = User::role('customer')->count();
        $verifiedCustomers = User::role('customer')->whereNotNull('email_verified_at')->count();

        $thisMonthStart        = now()->startOfMonth();
        $newCustomersThisMonth = User::role('customer')->where('created_at', '>=', $thisMonthStart)->count();
        $newVendorsThisMonth   = Tenant::where('created_at', '>=', $thisMonthStart)->count();

        $phs = PlatformHealthService::compute(
            totalVendors:          $vendorStats['total'],
            activeVendors:         $vendorStats['active'],
            totalCustomers:        $customerCount,
            verifiedCustomers:     $verifiedCustomers,
            newCustomersThisMonth: $newCustomersThisMonth,
            newVendorsThisMonth:   $newVendorsThisMonth,
        );

        // --- Vendor Growth: last 6 months ---
        $vendorGrowth = $this->getMonthlyGrowth(
            Tenant::query(),
            'created_at',
            6
        );

        // --- Customer Growth: last 6 months ---
        $customerGrowth = $this->getMonthlyGrowth(
            User::role('customer'),
            'created_at',
            6
        );

        // --- Recent Vendor Registrations (last 5) ---
        $recentVendors = Tenant::with('domains')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'name'       => $t->name,
                'email'      => $t->email,
                'is_approved'=> $t->is_approved,
                'domain'     => $t->domains->first()?->domain,
                'created_at' => $t->created_at?->toDateTimeString(),
            ]);

        // --- Recent Customer Signups (last 5) ---
        $recentCustomers = User::role('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($u) => [
                'id'         => $u->id,
                'name'       => $u->name,
                'email'      => $u->email,
                'created_at' => $u->created_at?->toDateTimeString(),
            ]);

        // --- Pending Vendors (for the approvals panel) ---
        $pendingVendors = Tenant::with('domains')
            ->where('is_approved', false)
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'name'       => $t->name,
                'email'      => $t->email,
                'domain'     => $t->domains->first()?->domain,
                'created_at' => $t->created_at?->toDateTimeString(),
            ]);

        return inertia('admin/Dashboard', [
            'vendorStats'     => $vendorStats,
            'customerCount'   => $customerCount,
            'vendorGrowth'    => $vendorGrowth,
            'customerGrowth'  => $customerGrowth,
            'recentVendors'   => $recentVendors,
            'recentCustomers' => $recentCustomers,
            'pendingVendors'  => $pendingVendors,
            'healthScore'     => $phs['score'],
            'healthComponents'=> $phs['components'],
        ]);
    }

    /**
     * Returns an array of { month, count } for the last $months months.
     * Works with any Eloquent builder.
     */
    private function getMonthlyGrowth($query, string $dateColumn, int $months): array
    {
        $rows = (clone $query)
            ->select(
                DB::raw("DATE_FORMAT({$dateColumn}, '%Y-%m') as month"),
                DB::raw('COUNT(*) as count')
            )
            ->where($dateColumn, '>=', now()->subMonths($months - 1)->startOfMonth())
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('count', 'month')
            ->toArray();

        // Fill in any missing months with zero so the chart is always 6 points
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $result[] = [
                'month' => now()->subMonths($i)->format('M Y'),
                'count' => $rows[$key] ?? 0,
            ];
        }

        return $result;
    }
}