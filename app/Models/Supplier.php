<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_email',
        'phone',
        'metadata',
        'address',
        'contact_person',
        'tax_id',
        'payment_terms',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the products for this supplier
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_supplier')
            ->withPivot('cost', 'supplier_sku')
            ->withTimestamps();
    }

    /**
     * Get the expenses for this supplier
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Get inventory expenses for this supplier
     */
    public function inventoryExpenses()
    {
        return $this->hasMany(Expense::class)->where('category', 'inventory');
    }

    /**
     * Get total spent with this supplier
     */
    public function getTotalSpentAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    /**
     * Get total inventory spent with this supplier
     */
    public function getTotalInventorySpentAttribute()
    {
        return $this->expenses()->where('category', 'inventory')->sum('amount');
    }

    /**
     * Scope a query to only include active suppliers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to search suppliers by name
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('contact_email', 'like', "%{$search}%")
            ->orWhere('phone', 'like', "%{$search}%");
    }

    /**
     * Get formatted contact info
     */
    public function getContactInfoAttribute(): string
    {
        $info = [];
        
        if ($this->contact_person) {
            $info[] = $this->contact_person;
        }
        
        if ($this->contact_email) {
            $info[] = $this->contact_email;
        }
        
        if ($this->phone) {
            $info[] = $this->phone;
        }
        
        return implode(' • ', $info);
    }

    /**
     * Get supplier status badge
     */
    public function getStatusBadgeAttribute(): array
    {
        if ($this->is_active) {
            return ['bg-emerald-100', 'text-emerald-800', 'Active'];
        }
        
        return ['bg-gray-100', 'text-gray-800', 'Inactive'];
    }
}