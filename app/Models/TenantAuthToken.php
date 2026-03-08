<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TenantAuthToken extends Model
{
    /**
     * The connection that should be used by the model.
     */
    protected $connection = 'central';

    /**
     * The primary key for the model.
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'token',
        'tenant_id',
        'authenticatable_id',
        'authenticatable_type',
        'expires_at',
        'used',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used' => 'boolean',
        ];
    }

    /**
     * Get the authenticatable entity that owns the token.
     */
    public function authenticatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to find valid, unused tokens.
     */
    public function scopeValid($query)
    {
        return $query->where('used', false)
            ->where('expires_at', '>', now());
    }
}
