<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->string('method')->default('Cash');
            $table->string('status')->default('Paid'); // Paid | Pending | Scheduled
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index('date');
            $table->index('status');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->dropIfExists('expenses');
    }
};
