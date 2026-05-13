<?php

namespace App\Http\Middleware;

use App\Models\TenantAuthToken;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConsumeTenantAuthToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if there's an SSO token in the query string
        if ($request->has('sso_token')) {
            $tokenString = $request->input('sso_token');

            // Find the token record in the CENTRAL database
            $tokenRecord = TenantAuthToken::where('token', $tokenString)
                ->where('tenant_id', tenant()->id)
                ->where('used', false)
                ->where('expires_at', '>', now())
                ->first();

            if ($tokenRecord) {
                // Mark token as used (one-time use)
                $tokenRecord->update(['used' => true]);

                // Get the authenticated user from the central database
                $centralUser = $tokenRecord->authenticatable;

                // Now we're already in the tenant context, so find the copied user
                $tenantUser = User::where('email', $centralUser->email)->first();

                if ($tenantUser) {
                    // Ensure the tenant user has the vendor role — the role seeder
                    // must have run before this point; assign if somehow missing.
                    if (! $tenantUser->hasRole('vendor')) {
                        $tenantUser->assignRole('vendor');
                    }

                    // Log the user into the tenant context
                    auth()->login($tenantUser, remember: false);

                    // Redirect to remove the token from the URL
                    return redirect('/vendor/dashboard');
                }
            }
        }

        return $next($request);
    }
}
