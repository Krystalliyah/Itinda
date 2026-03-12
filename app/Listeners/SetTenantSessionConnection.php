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
        // We will keep sessions in the central database to ensure stability 
        // across subdomains and prevent 419 CSRF issues.
        
        /*
        config(['session.connection' => 'tenant']);
        $currentTenant = tenant();
        if ($currentTenant) {
            config(['session.cookie' => 'itinda_session_' . $currentTenant->id]);
        }
        */
    }
}
