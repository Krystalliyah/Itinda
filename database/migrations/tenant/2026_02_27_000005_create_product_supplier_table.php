<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductSupplierTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_supplier', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->decimal('cost', 10, 2)->nullable();
            $table->string('supplier_sku')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'supplier_id'], 'product_supplier_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_supplier');
    }
}
