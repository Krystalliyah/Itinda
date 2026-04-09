<?php

use App\Http\Controllers\Vendor\InventoryController;
use App\Http\Controllers\Vendor\ProductController;
use App\Models\InventoryDeletionLog;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('deleting an inventory row records an audit log', function () {
    $product = Product::factory()->create([
        'stock' => 18,
        'price' => 150.50,
    ]);

    $controller = new InventoryController;
    $response = $controller->destroy($product);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    $this->assertDatabaseHas('inventory_deletion_logs', [
        'product_id' => $product->id,
        'product_name' => $product->name,
        'stock_level' => 18,
        'deleted_by_id' => $this->user->id,
    ]);

    $deletionLog = InventoryDeletionLog::where('product_id', $product->id)->first();
    expect($deletionLog)->not->toBeNull();
    expect($deletionLog->deleted_by)->toBe($this->user->login_id ?? $this->user->email);
    expect($deletionLog->total_value)->toBe(18 * 150.50);
    $response->assertRedirect();
});

test('deleting a product from products page also stores deletion audit details', function () {
    $product = Product::factory()->create([
        'stock' => 5,
        'price' => 299.99,
    ]);

    $controller = new ProductController;
    $response = $controller->destroy($product);

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    $this->assertDatabaseHas('inventory_deletion_logs', [
        'product_id' => $product->id,
        'product_name' => $product->name,
        'stock_level' => 5,
    ]);

    expect(InventoryDeletionLog::where('product_id', $product->id)->first()->deleted_by)
        ->toBe($this->user->login_id ?? $this->user->email);
    $response->assertRedirect();
});
