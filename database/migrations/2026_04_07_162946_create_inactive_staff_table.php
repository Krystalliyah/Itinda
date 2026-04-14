<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inactive_staff', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->string('email');
            $table->string('login_id');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('deactivated_by');
            $table->timestamp('deactivated_at');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('deactivated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inactive_staff');
    }
};
