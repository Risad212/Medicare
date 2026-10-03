<?php

namespace Tests\Unit;

use App\Services\BloodCompatibility;
use PHPUnit\Framework\TestCase;

class BloodCompatibilityTest extends TestCase
{
    public function test_universal_donor_can_donate_to_everyone(): void
    {
        foreach (['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'] as $recipient) {
            $this->assertTrue(
                BloodCompatibility::isCompatible('O-', $recipient),
                "O- should be compatible with {$recipient}"
            );
        }
    }

    public function test_universal_recipient_can_only_receive_ab_positive_from_ab_positive(): void
    {
        $this->assertTrue(BloodCompatibility::isCompatible('AB+', 'AB+'));
        $this->assertFalse(BloodCompatibility::isCompatible('AB+', 'O-'));
        $this->assertFalse(BloodCompatibility::isCompatible('AB+', 'A+'));
    }

    public function test_rh_negative_cannot_donate_to_rh_positive_of_other_systems_safely(): void
    {
        // A- -> A+, AB-, AB+ only (RBC rules in MATRIX)
        $this->assertTrue(BloodCompatibility::isCompatible('A-', 'A+'));
        $this->assertFalse(BloodCompatibility::isCompatible('A-', 'B+'));
        $this->assertFalse(BloodCompatibility::isCompatible('A-', 'O+'));
    }

    public function test_unknown_group_is_never_compatible(): void
    {
        $this->assertFalse(BloodCompatibility::isCompatible('X', 'A+'));
        $this->assertFalse(BloodCompatibility::isCompatible('O-', 'X'));
    }

    public function test_compatible_donors_for_ab_positive_includes_all_groups(): void
    {
        $donors = BloodCompatibility::compatibleDonorsFor('AB+');

        $this->assertEqualsCanonicalizing(
            ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
            $donors
        );
    }

    public function test_compatible_donors_for_o_negative_is_only_o_negative(): void
    {
        $this->assertSame(['O-'], BloodCompatibility::compatibleDonorsFor('O-'));
    }
}
