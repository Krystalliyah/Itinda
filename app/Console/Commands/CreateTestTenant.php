<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateTestTenant extends Command
{
    protected $signature = 'tenant:create-test {subdomain} {name} {email}';
    protected $description = 'Create a test tenant with subdomain';

    public function handle()
    {
        $subdomain = $this->argument('subdomain');
        $name = $this->argument('name');
        $email = $this->argument('email');

        $domain = $subdomain . '.' . config('app.domain');

        $this->info("Creating tenant: {$name}");
        $this->info("Domain: {$domain}");

        // Create tenant
        $tenant = Tenant::create([
            'id' => Str::slug($subdomain),
            'name' => $name,
            'email' => $email,
        ]);

        // Create domain
        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        $this->info("✓ Tenant created successfully!");
        $this->info("✓ Database: tenant{$tenant->id}");
        $this->info("✓ Access at: http://{$domain}");
        
        return Command::SUCCESS;
    }
}
