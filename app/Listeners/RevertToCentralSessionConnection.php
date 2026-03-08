<?php

namespace App\Listeners;

use Stancl\Tenancy\Events\TenancyEnded;

class RevertToCentralSessionConnection
{
    /**
     * Handle the event when tenancy ends.
     * Revert the session storage back to the central database.
     */
    public function handle(TenancyEnded $event): void
    {
        // Revert session configuration back to central
        config(['session.connection' => 'central']);

        // Revert to default session cookie name
        $defaultCookieName = \Illuminate\Support\Str::slug(env('APP_NAME', 'laravel')) . '-session';
        config(['session.cookie' => $defaultCookieName]);
    }
}
