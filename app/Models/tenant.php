<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return ['id', 'name', 'email', 'is_approved', 'user_id', 'operating_hours'];
    }

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'operating_hours' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}