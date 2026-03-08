<?php

namespace App\Listeners;

use Stancl\Tenancy\Events\TenancyInitialized;

class SetTenantSessionConnection
{
    /**
     * Handle the event when a tenant is initialized.
     * Switch the session storage to the tenant's database.
     */
    public function handle(TenancyInitialized $event): void
    {
        // Switch session storage to the tenant database connection
        config(['session.connection' => 'tenant']);

        // Use a tenant-specific session cookie name for extra isolation
        // This prevents any accidental cookie reuse across tenants
        $currentTenant = tenant();
        if ($currentTenant) {
            config(['session.cookie' => 'storekoto_session_' . $currentTenant->id]);
        }
    }
}
