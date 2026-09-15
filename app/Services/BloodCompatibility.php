<?php

namespace App\Services;

/**
 * Basic ABO/Rh red-blood-cell donor → recipient compatibility.
 *
 * IMPORTANT: This is a software screening aid only, never medical advice.
 * Authorized hospital staff must still approve every actual issue.
 */
class BloodCompatibility
{
    /**
     * Donor group => recipient groups that may safely receive it (RBC).
     *
     * @var array<string, list<string>>
     */
    protected const MATRIX = [
        'O-' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
        'O+' => ['O+', 'A+', 'B+', 'AB+'],
        'A-' => ['A-', 'A+', 'AB-', 'AB+'],
        'A+' => ['A+', 'AB+'],
        'B-' => ['B-', 'B+', 'AB-', 'AB+'],
        'B+' => ['B+', 'AB+'],
        'AB-' => ['AB-', 'AB+'],
        'AB+' => ['AB+'],
    ];

    public static function isCompatible(string $donorGroup, string $recipientGroup): bool
    {
        return in_array($recipientGroup, self::MATRIX[$donorGroup] ?? [], true);
    }

    /**
     * Recipient groups that can donate TO the given recipient group.
     */
    public static function compatibleDonorsFor(string $recipientGroup): array
    {
        if ($recipientGroup === '' || $recipientGroup === null) {
            return [];
        }

        return collect(self::MATRIX)
            ->filter(fn (array $recipients) => in_array($recipientGroup, $recipients, true))
            ->keys()
            ->all();
    }
}
