<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

/**
 * Conditionally initialize tenancy only when the current request
 * is on a tenant subdomain. If the request is from a central domain
 * (itinda.test, localhost, etc.), tenancy is skipped entirely.
 *
 * This middleware is added to Fortify so that auth routes (/login, 
 * /register, etc.) work correctly on BOTH central and tenant domains
 * without throwing TenantCouldNotBeIdentifiedOnDomainException.
 */
class InitializeTenancyIfTenantDomain
{
    public function handle(Request $request, Closure $next)
    {
        $centralDomains = config('tenancy.central_domains', []);

        // If we are on the central domain, skip tenancy entirely
        if (in_array($request->getHost(), $centralDomains)) {
            return $next($request);
        }

        // On a tenant subdomain — initialize tenancy normally
        return app(InitializeTenancyByDomain::class)->handle($request, $next);
    }
}
