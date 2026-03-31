<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $hasStore = false;
        $storeIsApproved = false;

        // Check store status
        if (function_exists('tenant') && tenant()) {
            // If we are on a subdomain, the store definitively exists and is approved
            $hasStore = true;
            $storeIsApproved = true;
        } elseif ($user && $user->hasRole('vendor')) {
            // Central domain check for vendors
            $tenant = \App\Models\Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $hasStore = true;
                $storeIsApproved = $tenant->is_approved;
            }
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
                'roles' => $user ? $user->getRoleNames() : [],
                'permissions' => $user ? $user->getAllPermissions()->pluck('name') : [],
                'hasStore' => $hasStore,
                'storeIsApproved' => $storeIsApproved,
                'tenant_id' => function_exists('tenant') && tenant() ? tenant()->getTenantKey() : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
                'vendor_approved' => fn () => $request->session()->get('vendor_approved'),
            ],
        ];
    }
}
