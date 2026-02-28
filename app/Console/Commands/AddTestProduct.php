<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class AddTestProduct extends Command
{
    protected $signature = 'tenant:add-test-product {tenant_id}';
    protected $description = 'Add a test product to a tenant database';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        
        tenancy()->initialize($tenantId);
        
        $product = Product::create([
            'name' => 'Chicken Inasal',
            'description' => 'Grilled chicken with special marinade',
            'price' => 150.00,
            'stock' => 50,
        ]);
        
        $this->info("Product created in tenant{$tenantId} database:");
        $this->line("  ID: {$product->id}");
        $this->line("  Name: {$product->name}");
        $this->line("  Price: ₱{$product->price}");
        $this->line("  Stock: {$product->stock}");
        
        return 0;
    }
}
