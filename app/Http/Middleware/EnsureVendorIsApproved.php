<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class EnsureVendorIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        // Must be a vendor
        if (! $user->hasRole('vendor')) {
            abort(403, 'Unauthorized action.');
        }

        // Check if they have an approved store
        // If on a tenant subdomain, use the current tenant directly
        // Otherwise, look up by user_id (for central domain users)
        if (tenancy()->initialized) {
            $tenant = tenant();
        } else {
            $tenant = Tenant::where('user_id', $user->id)->first();
        }

        if (! $tenant || ! $tenant->is_approved) {
            // If they are not approved, they can only see the dashboard setup page
            if (! $request->routeIs('vendor.dashboard') && ! $request->routeIs('vendor.store.create')) {
                return redirect()->route('vendor.dashboard')->with('error', 'Your store must be approved to access this page.');
            }
        }

        return $next($request);
    }
}
