<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'title',
        'category',
        'amount',
        'date',
        'method',
        'status',
        'note',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'float',
    ];
}
