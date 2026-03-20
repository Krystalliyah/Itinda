<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'total',
        'status',
        'placed_at',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}