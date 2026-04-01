<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->table('suppliers', function (Blueprint $table) {
            // Add contact_person if not exists
            if (!Schema::connection($this->connection)->hasColumn('suppliers', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('phone');
            }
            
            // Add address if not exists
            if (!Schema::connection($this->connection)->hasColumn('suppliers', 'address')) {
                $table->text('address')->nullable()->after('contact_person');
            }
            
            // Add tax_id if not exists
            if (!Schema::connection($this->connection)->hasColumn('suppliers', 'tax_id')) {
                $table->string('tax_id')->nullable()->after('address');
            }
            
            // Add payment_terms if not exists
            if (!Schema::connection($this->connection)->hasColumn('suppliers', 'payment_terms')) {
                $table->string('payment_terms')->nullable()->after('tax_id');
            }
            
            // Add is_active if not exists
            if (!Schema::connection($this->connection)->hasColumn('suppliers', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('payment_terms');
                $table->index('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('suppliers', function (Blueprint $table) {
            $columns = [
                'contact_person',
                'address',
                'tax_id',
                'payment_terms',
                'is_active',
            ];
            
            foreach ($columns as $column) {
                if (Schema::connection($this->connection)->hasColumn('suppliers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};