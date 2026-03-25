<?php

namespace App\Services;

/**
 * Platform Health Score (PHS) — canonical formula
 *
 * PHS = (activeVendorRatio × 40) + (emailVerificationRate × 30)
 *     + (newCustomersScore × 20) + (newVendorsScore × 10)
 *
 * Component caps:
 *   activeVendorRatio    = approved / total vendors          → 0–40 pts
 *   emailVerificationRate = verified / total customers       → 0–30 pts
 *   newCustomersScore    = min(newCustomersThisMonth / 10, 1) → 0–20 pts
 *   newVendorsScore      = min(newVendorsThisMonth  /  5, 1) → 0–10 pts
 *
 * Total: 0–100
 */
class PlatformHealthService
{
    /**
     * Compute the PHS and return the score plus each component's contribution.
     *
     * @param  int  $totalVendors
     * @param  int  $activeVendors
     * @param  int  $totalCustomers
     * @param  int  $verifiedCustomers
     * @param  int  $newCustomersThisMonth
     * @param  int  $newVendorsThisMonth
     * @return array{score: int, components: array}
     */
    public static function compute(
        int $totalVendors,
        int $activeVendors,
        int $totalCustomers,
        int $verifiedCustomers,
        int $newCustomersThisMonth,
        int $newVendorsThisMonth,
    ): array {
        $activeVendorRatio     = $totalVendors   > 0 ? $activeVendors    / $totalVendors   : 0;
        $emailVerificationRate = $totalCustomers > 0 ? $verifiedCustomers / $totalCustomers : 0;
        $newCustomersScore     = min($newCustomersThisMonth / 10, 1);
        $newVendorsScore       = min($newVendorsThisMonth  /  5, 1);

        $score = (int) round(
            ($activeVendorRatio     * 40) +
            ($emailVerificationRate * 30) +
            ($newCustomersScore     * 20) +
            ($newVendorsScore       * 10)
        );

        return [
            'score'      => min($score, 100),
            'components' => [
                'activeVendorRatio'     => round($activeVendorRatio     * 100),
                'emailVerificationRate' => round($emailVerificationRate * 100),
                'newCustomersScore'     => round($newCustomersScore     * 100),
                'newVendorsScore'       => round($newVendorsScore       * 100),
            ],
        ];
    }

    /**
     * Return the label and hex color for a given score.
     */
    public static function label(int $score): array
    {
        if ($score >= 80) return ['text' => 'Excellent',       'color' => '#10b981'];
        if ($score >= 60) return ['text' => 'Good',            'color' => '#3b82f6'];
        if ($score >= 40) return ['text' => 'Fair',            'color' => '#f59e0b'];
        return                   ['text' => 'Needs Attention', 'color' => '#ef4444'];
    }
}
