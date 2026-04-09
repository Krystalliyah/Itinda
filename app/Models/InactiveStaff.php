<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InactiveStaff extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'email',
        'login_id',
        'reason',
        'deactivated_by',
        'deactivated_at',
    ];

    protected $casts = [
        'deactivated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function deactivatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deactivated_by');
    }
}
