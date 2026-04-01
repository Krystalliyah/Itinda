<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'title',
        'category',
        'amount',
        'date',
        'method',
        'status',
        'note',
        'user_id',
        'tenant_id',
        'reference_number',
        'supplier_id',
        'receipt_path',
        'is_recurring',
        'recurring_frequency',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
    ];

    /**
     * Get the user who recorded this expense
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tenant this expense belongs to
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the supplier for this expense
     */
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Scope a query to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by inventory category
     */
    public function scopeInventory($query)
    {
        return $query->where('category', 'inventory');
    }

    /**
     * Scope a query to filter by status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by payment method
     */
    public function scopePaymentMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Scope a query to filter by supplier
     */
    public function scopeSupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Get formatted amount with currency symbol
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₱' . number_format($this->amount, 2);
    }

    /**
     * Get formatted date
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date ? $this->date->format('M d, Y') : '';
    }

    /**
     * Get short date format
     */
    public function getShortDateAttribute(): string
    {
        return $this->date ? $this->date->format('Y-m-d') : '';
    }

    /**
     * Check if this is an inventory expense
     */
    public function isInventory(): bool
    {
        return $this->category === 'inventory';
    }

    /**
     * Get expense status badge
     */
    public function getStatusBadgeAttribute(): array
    {
        $badges = [
            'pending' => ['bg-yellow-100', 'text-yellow-800', 'Pending'],
            'approved' => ['bg-green-100', 'text-green-800', 'Approved'],
            'rejected' => ['bg-red-100', 'text-red-800', 'Rejected'],
            'paid' => ['bg-emerald-100', 'text-emerald-800', 'Paid'],
        ];

        return $badges[$this->status] ?? ['bg-gray-100', 'text-gray-800', ucfirst($this->status)];
    }

    /**
     * Get payment method badge
     */
    public function getPaymentMethodBadgeAttribute(): array
    {
        $methods = [
            'cash' => ['bg-blue-100', 'text-blue-800', 'Cash'],
            'bank_transfer' => ['bg-purple-100', 'text-purple-800', 'Bank Transfer'],
            'credit_card' => ['bg-indigo-100', 'text-indigo-800', 'Credit Card'],
            'check' => ['bg-gray-100', 'text-gray-800', 'Check'],
        ];

        return $methods[$this->method] ?? ['bg-gray-100', 'text-gray-800', ucfirst($this->method)];
    }
}