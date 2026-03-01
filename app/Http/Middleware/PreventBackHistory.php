<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent browser caching of authenticated pages
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        // If this was a logout POST and the user is now guest, instruct Inertia
        // to perform a full page redirect to the central login page so that
        // client-side history/state is replaced and back/forward won't show
        // the protected dashboard.
        if ($request->isMethod('post') && $request->is('logout') && auth()->guest()) {
            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: 'http';
            $location = $scheme . '://' . config('app.domain') . '/login';

            $response->headers->set('X-Inertia-Location', $location);
            $response->setStatusCode(409);
        }

        return $response;
    }
}
