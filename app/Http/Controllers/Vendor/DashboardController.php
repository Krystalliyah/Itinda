<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Find the tenant associated with this user
        $tenant = Tenant::where('user_id', $user->id)->first();

        if ($tenant && $tenant->is_approved) {
            // Get the domain
            $domain = $tenant->domains()->first();

            // Default credentials for tenant admin
            $defaultLoginId = 'admin-' . $tenant->id;
            $defaultPassword = 'Password123!';

            return Inertia::render('vendor/Approved', [
                'subdomain' => $domain ? $domain->domain : null,
                'tenant' => $tenant,
                'defaultCredentials' => [
                    'login_id' => $defaultLoginId,
                    'password' => $defaultPassword,
                ],
            ]);
        }

        // If no tenant or not approved, show the setup dashboard
        return Inertia::render('vendor/Dashboard');
    }
}