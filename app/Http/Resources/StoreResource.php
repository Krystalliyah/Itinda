<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray($request): array
    {
        $operatingHours = $this->operating_hours;
        $data = $this->data ?? [];

        return [
            'id' => $this->id,
            'name' => $this->name ?? 'NA',
            'description' => $this->description ?? 'NA',
            'address' => $this->address ?? 'NA',
            'phone' => $this->phone ?? 'NA',
            'hours' => $this->formatOperatingHours($operatingHours),
            'isOpen' => $this->checkIfOpen($operatingHours),
            'logo' => $this->profile_photo_url ?? 'NA',
            'cover' => $this->cover_photo_url ?? 'NA',
            'is_pickup_only' => true,
        ];
    }

    private function formatOperatingHours($operatingHours): string
    {
        $default = 'No data available';

        if (empty($operatingHours) || ! is_array($operatingHours)) {
            return $default;
        }

        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $labels = [
            'monday' => 'Mon',
            'tuesday' => 'Tue',
            'wednesday' => 'Wed',
            'thursday' => 'Thu',
            'friday' => 'Fri',
            'saturday' => 'Sat',
            'sunday' => 'Sun',
        ];

        $normalized = [];

        foreach ($days as $day) {
            if (
                ! isset($operatingHours[$day]) ||
                ! isset($operatingHours[$day]['is_open']) ||
                ! $operatingHours[$day]['is_open']
            ) {
                $normalized[$day] = 'closed';

                continue;
            }

            $open = $this->formatTime($operatingHours[$day]['open_time'] ?? null);
            $close = $this->formatTime($operatingHours[$day]['close_time'] ?? null);

            if (! $open || ! $close) {
                $normalized[$day] = 'closed';

                continue;
            }

            $normalized[$day] = $open.' - '.$close;
        }

        $groups = [];
        $startDay = null;
        $endDay = null;
        $currentHours = null;

        foreach ($days as $day) {
            $hours = $normalized[$day];

            // skip closed days entirely
            if ($hours === 'closed') {
                if ($currentHours !== null) {
                    $groups[] = [
                        'start' => $startDay,
                        'end' => $endDay,
                        'hours' => $currentHours,
                    ];
                    $startDay = null;
                    $endDay = null;
                    $currentHours = null;
                }

                continue;
            }

            if ($currentHours === null) {
                $startDay = $day;
                $endDay = $day;
                $currentHours = $hours;

                continue;
            }

            if ($hours === $currentHours) {
                $endDay = $day;

                continue;
            }

            $groups[] = [
                'start' => $startDay,
                'end' => $endDay,
                'hours' => $currentHours,
            ];

            $startDay = $day;
            $endDay = $day;
            $currentHours = $hours;
        }

        if ($currentHours !== null) {
            $groups[] = [
                'start' => $startDay,
                'end' => $endDay,
                'hours' => $currentHours,
            ];
        }

        if (empty($groups)) {
            return $default;
        }

        $formatted = [];

        foreach ($groups as $group) {
            $startLabel = $labels[$group['start']];
            $endLabel = $labels[$group['end']];

            $dayRange = $group['start'] === $group['end']
                ? $startLabel
                : $startLabel.' - '.$endLabel;

            $formatted[] = $dayRange.': '.$group['hours'];
        }

        return implode(', ', $formatted);
    }

    private function checkIfOpen($operatingHours): bool
    {
        if (empty($operatingHours) || ! is_array($operatingHours)) {
            return false;
        }

        $now = now();
        $currentDay = strtolower($now->format('l'));
        $schedule = $operatingHours[$currentDay] ?? null;

        if (! $schedule || empty($schedule['is_open'])) {
            return false;
        }

        $openTime = $schedule['open_time'] ?? null;
        $closeTime = $schedule['close_time'] ?? null;

        if (! $openTime || ! $closeTime) {
            return false;
        }

        $currentTime = $now->format('H:i');

        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    private function formatTime(?string $time): ?string
    {
        if (! $time) {
            return null;
        }

        try {
            return Carbon::createFromFormat('H:i', $time)->format('gA');
        } catch (\Exception $e) {
            return $time;
        }
    }
}
