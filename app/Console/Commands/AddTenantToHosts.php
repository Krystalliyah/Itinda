<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;

class AddTenantToHosts extends Command
{
    protected $signature = 'tenants:add-to-hosts';
    protected $description = 'Add all tenant subdomains to Windows hosts file';

    public function handle()
    {
        $hostsFile = 'C:\Windows\System32\drivers\etc\hosts';
        
        if (!is_writable($hostsFile)) {
            $this->error('Hosts file is not writable. Run this command as Administrator.');
            return 1;
        }

        $tenants = Tenant::with('domains')->get();
        $entries = [];

        foreach ($tenants as $tenant) {
            foreach ($tenant->domains as $domain) {
                $entries[] = "127.0.0.1 {$domain->domain}";
            }
        }

        if (empty($entries)) {
            $this->info('No tenants found.');
            return 0;
        }

        $content = file_get_contents($hostsFile);
        
        // Add marker comments
        $marker = "# iTinda Tenants - Auto-generated";
        $endMarker = "# End iTinda Tenants";
        
        // Remove old entries
        $content = preg_replace("/$marker.*?$endMarker\n/s", '', $content);
        
        // Add new entries
        $newEntries = "\n$marker\n" . implode("\n", $entries) . "\n$endMarker\n";
        file_put_contents($hostsFile, $content . $newEntries);

        $this->info('Added ' . count($entries) . ' tenant domains to hosts file:');
        foreach ($entries as $entry) {
            $this->line("  $entry");
        }

        return 0;
    }
}
