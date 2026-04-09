<?php

use Illuminate\Support\Facades\DB;

require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$tenants = DB::table('tenants')->get();
echo 'Tenants found: '.count($tenants)."\n";
foreach ($tenants as $tenant) {
    echo "ID: {$tenant->id}, Name: {$tenant->name}, Email: {$tenant->email}\n";
}

$domains = DB::table('domains')->get();
echo "\nDomains found: ".count($domains)."\n";
foreach ($domains as $domain) {
    echo "Domain: {$domain->domain}, Tenant: {$domain->tenant_id}\n";
}
