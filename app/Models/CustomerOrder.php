<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerOrder extends Model
{
    protected $connection = 'central';

    protected $table = 'customer_orders';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'order_id',
        'order_number',
        'total',
        'status',
        'ordered_at',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'ordered_at' => 'datetime',
    ];
}