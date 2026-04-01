<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'tenant';

    public function up(): void
    {
        Schema::connection($this->connection)->table('expenses', function (Blueprint $table) {
            // Add user_id for tracking who created the expense
            if (!Schema::connection($this->connection)->hasColumn('expenses', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id');
                $table->index('user_id');
            }
            
            // Add tenant_id for multi-tenant context
            if (!Schema::connection($this->connection)->hasColumn('expenses', 'tenant_id')) {
                $table->string('tenant_id')->nullable()->after('user_id');
                $table->index('tenant_id');
            }
            
            // Add supplier_id for linking to suppliers table
            if (!Schema::connection($this->connection)->hasColumn('expenses', 'supplier_id')) {
                $table->foreignId('supplier_id')->nullable()->after('note');
                $table->index('supplier_id');
            }
            
            // Add reference_number for invoice/PO tracking
            if (!Schema::connection($this->connection)->hasColumn('expenses', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('supplier_id');
                $table->index('reference_number');
            }
            
            // Add receipt_path for storing uploaded receipts
            if (!Schema::connection($this->connection)->hasColumn('expenses', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('reference_number');
            }
            
            // Add recurring expense support
            if (!Schema::connection($this->connection)->hasColumn('expenses', 'is_recurring')) {
                $table->boolean('is_recurring')->default(false)->after('receipt_path');
            }
            
            if (!Schema::connection($this->connection)->hasColumn('expenses', 'recurring_frequency')) {
                $table->string('recurring_frequency')->nullable()->after('is_recurring');
            }
        });
    }

    public function down(): void
    {
        Schema::connection($this->connection)->table('expenses', function (Blueprint $table) {
            $columns = [
                'user_id',
                'tenant_id',
                'supplier_id',
                'reference_number',
                'receipt_path',
                'is_recurring',
                'recurring_frequency',
            ];
            
            foreach ($columns as $column) {
                if (Schema::connection($this->connection)->hasColumn('expenses', $column)) {
                    if (in_array($column, ['user_id', 'supplier_id'])) {
                        $table->dropForeign([$column]);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};