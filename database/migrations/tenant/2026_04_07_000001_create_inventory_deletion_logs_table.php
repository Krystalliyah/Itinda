<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryDeletionLogsTable extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name');
            $table->integer('stock_level')->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('total_value', 16, 2)->default(0);
            $table->unsignedBigInteger('deleted_by_id')->nullable();
            $table->string('deleted_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_deletion_logs');
    }
}
