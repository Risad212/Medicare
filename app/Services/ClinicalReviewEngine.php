<?php

namespace App\Services;

/**
 * Clinical Review Engine — advisory-only safety flags for prescribers.
 *
 * Scope (rules v1.0.0 — deliberately narrow, defined without clinician
 * sign-off, so it makes no pharmacology claims):
 * - allergy-overlap: a medicine name overlaps patient-recorded allergy text.
 * - polypharmacy-check: unusually many items — a completeness nudge.
 * - missing-dosage-details: items lacking dosage/frequency/instructions.
 *
 * Hard rules:
 * - Alerts are ADVISORY. The engine never blocks saving; the prescribing
 *   clinician owns every clinical decision.
 * - No interaction, contraindication, or dosing advice. Anything beyond
 *   record-overlap checks requires clinician-defined rules plus a
 *   RULES_VERSION bump reviewed with clinical staff.
 * - Every alert carries its rule id, the rules version, and references so
 *   it stays explainable on screen.
 */
class ClinicalReviewEngine
{
    public const RULES_VERSION = '1.0.0';

    public const POLYPHARMACY_THRESHOLD = 5;

    /**
     * Review prescription items against patient-recorded allergies.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array{rule: string, version: string, severity: string, message: string, references: array<int, string>}>
     */
    public static function review(array $items, ?string $recordedAllergies): array
    {
        $alerts = [];

        $overlap = self::allergyOverlap($items, $recordedAllergies);
        if ($overlap !== null) {
            $alerts[] = $overlap;
        }

        $polypharmacy = self::polypharmacyCheck($items);
        if ($polypharmacy !== null) {
            $alerts[] = $polypharmacy;
        }

        $dosage = self::missingDosageDetails($items);
        if ($dosage !== null) {
            $alerts[] = $dosage;
        }

        return $alerts;
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{rule: string, version: string, severity: string, message: string, references: array<int, string>}|null
     */
    private static function allergyOverlap(array $items, ?string $recordedAllergies): ?array
    {
        $terms = self::allergyTerms($recordedAllergies);

        if ($terms === []) {
            return null;
        }

        $hits = [];

        foreach ($items as $item) {
            $name = mb_strtolower(trim((string) ($item['medicine_name'] ?? '')));

            if ($name === '') {
                continue;
            }

            foreach ($terms as $term) {
                if (str_contains($name, $term) || str_contains($term, $name)) {
                    $hits[$term] = trim((string) ($item['medicine_name'] ?? $name));
                }
            }
        }

        if ($hits === []) {
            return null;
        }

        $pairs = [];
        foreach ($hits as $term => $medicine) {
            $pairs[] = "'{$medicine}' may overlap recorded allergy '{$term}'";
        }

        return [
            'rule' => 'allergy-overlap',
            'version' => self::RULES_VERSION,
            'severity' => 'review',
            'message' => 'Possible allergy overlap: '.implode('; ', $pairs).'. Verify with the patient before prescribing.',
            'references' => ['Patient-recorded allergies (profile)'],
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function allergyTerms(?string $recordedAllergies): array
    {
        if (! is_string($recordedAllergies) || trim($recordedAllergies) === '') {
            return [];
        }

        $terms = preg_split('/[,;]+/', mb_strtolower($recordedAllergies)) ?: [];
        $terms = array_map(static fn (string $term): string => trim($term), $terms);

        return array_values(array_unique(array_filter(
            $terms,
            static fn (string $term): bool => mb_strlen($term) >= 3
        )));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{rule: string, version: string, severity: string, message: string, references: array<int, string>}|null
     */
    private static function polypharmacyCheck(array $items): ?array
    {
        $count = count(array_filter(
            $items,
            static fn ($item): bool => trim((string) ($item['medicine_name'] ?? '')) !== ''
        ));

        if ($count <= self::POLYPHARMACY_THRESHOLD) {
            return null;
        }

        return [
            'rule' => 'polypharmacy-check',
            'version' => self::RULES_VERSION,
            'severity' => 'info',
            'message' => "{$count} medicines listed (more than ".self::POLYPHARMACY_THRESHOLD.'). Double-check the list with the patient — this is a completeness nudge, not a clinical judgment.',
            'references' => ['Prescription item count'],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array{rule: string, version: string, severity: string, message: string, references: array<int, string>}|null
     */
    private static function missingDosageDetails(array $items): ?array
    {
        $incomplete = [];

        foreach ($items as $item) {
            $name = trim((string) ($item['medicine_name'] ?? ''));

            if ($name === '') {
                continue;
            }

            $hasDetails = trim((string) ($item['dosage'] ?? '')) !== ''
                || trim((string) ($item['frequency'] ?? '')) !== ''
                || trim((string) ($item['instructions'] ?? '')) !== '';

            if (! $hasDetails) {
                $incomplete[] = $name;
            }
        }

        if ($incomplete === []) {
            return null;
        }

        return [
            'rule' => 'missing-dosage-details',
            'version' => self::RULES_VERSION,
            'severity' => 'info',
            'message' => 'No dosage details for: '.implode(', ', array_unique($incomplete)).'. Add dosage, frequency, or instructions so dispensing is unambiguous.',
            'references' => ['Prescription item fields'],
        ];
    }
}
