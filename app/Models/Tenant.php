<?php

namespace App\Models;

use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'description',
            'address',
            'phone',
            'is_approved',
            'user_id',
            'operating_hours',
            'profile_photo_path',
            'cover_photo_path',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $table = 'tenants';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'data' => 'array',
        'operating_hours' => 'array',
        'is_approved' => 'boolean',
    ];

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        // Check S3 first if configured, otherwise fallback to public
        if (config('filesystems.disks.s3.key') && \Storage::disk('s3')->exists($this->profile_photo_path)) {
            return \Storage::disk('s3')->url($this->profile_photo_path);
        }

        return \Storage::disk('public')->url($this->profile_photo_path);
    }

    public function getCoverPhotoUrlAttribute(): ?string
    {
        if (! $this->cover_photo_path) {
            return null;
        }

        if (config('filesystems.disks.s3.key') && \Storage::disk('s3')->exists($this->cover_photo_path)) {
            return \Storage::disk('s3')->url($this->cover_photo_path);
        }

        return \Storage::disk('public')->url($this->cover_photo_path);
    }
}
