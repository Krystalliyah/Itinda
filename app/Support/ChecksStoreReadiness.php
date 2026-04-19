<?php

namespace App\Support;

use App\Models\Tenant;

trait ChecksStoreReadiness
{
    private function hasValidOperatingHours($hours): bool
    {
        if (!is_array($hours)) {
            return false;
        }

        $orderedDays = [
            'monday',
            'tuesday',
            'wednesday',
            'thursday',
            'friday',
            'saturday',
            'sunday',
        ];

        foreach ($orderedDays as $dayKey) {
            $day = $hours[$dayKey] ?? null;

            if (!is_array($day) || !array_key_exists('is_open', $day)) {
                return false;
            }

            if ($day['is_open'] === false) {
                continue;
            }

            if (!filled($day['open_time'] ?? null) || !filled($day['close_time'] ?? null)) {
                return false;
            }
        }

        return true;
    }

    private function isStoreProfileComplete(Tenant $tenant): bool
    {
        return filled($tenant->name) && filled($tenant->description);
    }

    private function isContactDetailsComplete(Tenant $tenant): bool
    {
        return filled($tenant->email)
            && filled($tenant->phone)
            && filled($tenant->address);
    }

    private function isPickupScheduleComplete(Tenant $tenant): bool
    {
        return $this->hasValidOperatingHours($tenant->operating_hours);
    }

    private function isStoreReady(Tenant $tenant): bool
    {
        return $this->isStoreProfileComplete($tenant)
            && $this->isContactDetailsComplete($tenant)
            && $this->isPickupScheduleComplete($tenant);
    }
}