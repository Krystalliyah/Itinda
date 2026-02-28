<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $user = $request->user();

            // Redirect to appropriate dashboard based on role
            if ($request->is('dashboard')) {
                if ($user->hasRole('admin')) {
                    return redirect()->route('admin.dashboard');
                }
                
                if ($user->hasRole('vendor')) {
                    return redirect()->route('vendor.dashboard');
                }
                
                if ($user->hasRole('customer')) {
                    return redirect()->route('customer.dashboard');
                }
            }
        }

        return $next($request);
    }
}
