<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'central'; // مهم for multi-tenancy

    public function up(): void
    {
        Schema::connection($this->connection)->create('customer_orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');     // central.users.id
            $table->string('tenant_id');               // tenants.id
            $table->unsignedBigInteger('order_id');    // from tenant DB

            $table->string('order_number')->nullable();

            $table->decimal('total', 10, 2);
            $table->string('status');

            $table->timestamp('ordered_at');

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('tenant_id');
            $table->index(['tenant_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('customer_orders');
    }
};