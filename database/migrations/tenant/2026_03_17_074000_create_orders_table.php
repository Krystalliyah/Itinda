<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // central user

            $table->string('order_number')->unique();

            $table->decimal('total', 10, 2);

            $table->string('status')->default('pending');

            $table->timestamp('placed_at');

            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('orders');
    }
};