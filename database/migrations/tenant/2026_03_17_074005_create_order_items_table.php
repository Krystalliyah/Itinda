<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('order_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id');

            $table->string('product_name'); // snapshot
            $table->decimal('price', 10, 2);
            $table->integer('quantity');

            $table->decimal('total', 10, 2);

            $table->timestamps();

            $table->index('order_id');

            $table->foreign('order_id')
                  ->references('id')
                  ->on('orders')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('order_items');
    }
};