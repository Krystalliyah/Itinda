<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use HasFactory;

    protected $table = 'stores';

    protected $fillable = [
        'owner_id',
        'store_name',
        'address',
        'city',
        'latitude',
        'longitude',
        'phone',
        'operating_hours',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'owner_id');
    }

    public function staff()
    {
        return $this->hasMany(User::class, 'owner_id')->where('role', 'vendor_staff');
    }
}
