<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Store;

class EnsureVendorHasStoreAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        // Admin can access everything
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        if (! $user->hasRole('vendor')) {
            abort(403);
        }

        
        $storeParam = $request->route('store');

        $store = $storeParam instanceof Store
            ? $storeParam
            : Store::query()->findOrFail($storeParam);

        $isOwner = ($store->owner_id === $user->id);

        $isStaff = DB::table('store_staff')
            ->where('store_id', $store->id)
            ->where('user_id', $user->id)
            ->exists();

        if (! $isOwner && ! $isStaff) {
            abort(403, 'No access to this store.');
        }

        return $next($request);
    }
}
