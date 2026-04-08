<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDeletionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_name',
        'stock_level',
        'unit_price',
        'total_value',
        'deleted_by_id',
        'deleted_by',
        'notes',
    ];
}
