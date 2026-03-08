<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureVendorIsAdmin
{
    /**
     * Handle an incoming request.
     * Allow only authenticated users who have the vendor role and are flagged is_admin.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('vendor') || ! $user->is_admin) {
            abort(403);
        }

        return $next($request);
    }
}
