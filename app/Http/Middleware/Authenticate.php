<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * We use a plain path string ('/login') instead of route('login') because
     * in multi-tenant setups with domain-constrained route groups, the named
     * 'login' route may not be resolvable from all domain contexts.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : '/login';
    }
}
