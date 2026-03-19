<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        $operatingHours = $this->operating_hours;
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'name' => $this->name ?? 'NA',
            'address' => $this->address ?? 'NA',
            'phone' => $this->phone ?? 'NA',
            'hours' => $this->formatOperatingHours($operatingHours),
            'isOpen' => $this->checkIfOpen($operatingHours),
            'logo' => $data['logo'] ?? 'NA',
            'cover' => $data['cover'] ?? 'NA',
        ];
    }

    private function formatOperatingHours($operatingHours): string
    {
        if (empty($operatingHours) || !is_array($operatingHours)) {
            return 'Mon - Fri: 8AM - 5PM';
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        $formattedDays = [];

        foreach ($days as $day) {
            if (!isset($operatingHours[$day])) {
                continue;
            }

            $schedule = $operatingHours[$day];

            if (!isset($schedule['is_open']) || !$schedule['is_open']) {
                continue;
            }

            $open = $this->formatTime($schedule['open_time'] ?? null);
            $close = $this->formatTime($schedule['close_time'] ?? null);

            if ($open && $close) {
                $formattedDays[] = ucfirst(substr($day, 0, 3)) . ': ' . $open . ' - ' . $close;
            }
        }

        if (empty($formattedDays)) {
            return 'Mon - Fri: 8AM - 5PM';
        }

        return implode(', ', $formattedDays);
    }

    private function checkIfOpen($operatingHours): bool
    {
        if (empty($operatingHours) || !is_array($operatingHours)) {
            $now = now();
            $day = strtolower($now->format('l'));

            return in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'])
                && $now->format('H:i') >= '08:00'
                && $now->format('H:i') <= '17:00';
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));

        if (!isset($operatingHours[$currentDay])) {
            return false;
        }

        $schedule = $operatingHours[$currentDay];

        if (empty($schedule['is_open'])) {
            return false;
        }

        $openTime = $schedule['open_time'] ?? null;
        $closeTime = $schedule['close_time'] ?? null;

        if (!$openTime || !$closeTime) {
            return false;
        }

        $currentTime = $now->format('H:i');

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    private function formatTime(?string $time): ?string
    {
        if (!$time) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $time)->format('gA');
        } catch (\Exception $e) {
            return $time;
        }
    }
}