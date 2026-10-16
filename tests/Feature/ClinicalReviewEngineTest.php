<?php

namespace Tests\Feature;

use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\ClinicalReviewEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicalReviewEngineTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_alert_carries_rule_version_and_references(): void
    {
        $alerts = ClinicalReviewEngine::review(
            [['medicine_name' => 'Penicillin 500mg', 'dosage' => '', 'frequency' => '', 'instructions' => '']],
            'Penicillin'
        );

        $this->assertNotEmpty($alerts);

        foreach ($alerts as $alert) {
            $this->assertSame(ClinicalReviewEngine::RULES_VERSION, $alert['version']);
            $this->assertNotEmpty($alert['rule']);
            $this->assertNotEmpty($alert['message']);
            $this->assertNotEmpty($alert['references']);
        }
    }

    public function test_allergy_overlap_is_detected_case_insensitively(): void
    {
        $alerts = ClinicalReviewEngine::review(
            [['medicine_name' => 'PENICILLIN 500mg', 'dosage' => '1 tab', 'frequency' => 'BD', 'instructions' => 'After food']],
            'penicillin, peanuts'
        );

        $rules = array_column($alerts, 'rule');
        $this->assertContains('allergy-overlap', $rules);
        $this->assertSame('review', $alerts[array_search('allergy-overlap', $rules)]['severity']);
    }

    public function test_no_alerts_without_recorded_allergies_or_risk_factors(): void
    {
        $this->assertSame([], ClinicalReviewEngine::review(
            [['medicine_name' => 'Paracetamol', 'dosage' => '500mg', 'frequency' => 'TDS', 'instructions' => 'After food']],
            null
        ));
        $this->assertSame([], ClinicalReviewEngine::review([], 'Penicillin'));
    }

    public function test_short_allergy_terms_are_ignored_to_avoid_noise(): void
    {
        // 'Fe' would substring-match almost everything; terms under 3 chars are skipped.
        $alerts = ClinicalReviewEngine::review(
            [['medicine_name' => 'Ferric carboxymaltose', 'dosage' => '1 vial', 'frequency' => 'OD', 'instructions' => 'IV']],
            'Fe'
        );

        $this->assertNotContains('allergy-overlap', array_column($alerts, 'rule'));
    }

    public function test_polypharmacy_and_missing_dosage_flags_are_informational(): void
    {
        $items = [];
        for ($i = 1; $i <= 6; $i++) {
            $items[] = ['medicine_name' => "Medicine {$i}", 'dosage' => '', 'frequency' => '', 'instructions' => ''];
        }

        $alerts = ClinicalReviewEngine::review($items, null);
        $byRule = array_column($alerts, null, 'rule');

        $this->assertArrayHasKey('polypharmacy-check', $byRule);
        $this->assertArrayHasKey('missing-dosage-details', $byRule);
        $this->assertSame('info', $byRule['polypharmacy-check']['severity']);
        $this->assertSame('info', $byRule['missing-dosage-details']['severity']);
    }

    public function test_five_or_fewer_complete_items_raise_nothing(): void
    {
        $items = [];
        for ($i = 1; $i <= 5; $i++) {
            $items[] = ['medicine_name' => "Medicine {$i}", 'dosage' => '1 tab', 'frequency' => 'OD', 'instructions' => ''];
        }

        $this->assertSame([], ClinicalReviewEngine::review($items, null));
    }

    public function test_engine_never_blocks_on_garbage_input(): void
    {
        $alerts = ClinicalReviewEngine::review([[], ['medicine_name' => null]], '   ');

        $this->assertIsArray($alerts);
    }

    public function test_doctor_show_page_surfaces_advisory_alerts_without_blocking_care(): void
    {
        [$doctorUser, $doctor, $patient] = $this->makeDoctorWithPatient('Penicillin');

        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_user_id' => $patient->id,
            'patient_name' => $patient->name,
            'diagnosis' => 'Flu',
        ]);
        PrescriptionItem::create([
            'prescription_id' => $prescription->id,
            'medicine_name' => 'Penicillin 500mg',
        ]);

        $this->actingAs($doctorUser)
            ->get(route('doctor.prescriptions.show', $prescription))
            ->assertOk()
            ->assertSee('Clinical review (advisory only', false)
            ->assertSee('allergy-overlap', false)
            ->assertSee('v'.ClinicalReviewEngine::RULES_VERSION, false);
    }

    private function makeDoctorWithPatient(string $allergies): array
    {
        $doctorUser = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Review Doctor',
            'slug' => 'review-doctor-'.uniqid(),
            'user_id' => $doctorUser->id,
            'status' => 1,
        ]);
        $patient = User::factory()->create(['role' => 'patient', 'allergies' => $allergies]);

        return [$doctorUser, $doctor, $patient];
    }
}
