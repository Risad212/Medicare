<?php

namespace Tests\Feature;

use App\Models\LabOrder;
use App\Models\LabReport;
use App\Services\GuestRecordLinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class PatientLabReportsTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function makeOrderForPatient(int $userId, string $email): LabOrder
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest();

        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'user_id' => $userId,
            'email' => $email,
            'patient_name' => 'Patient Name',
            'priority' => 'normal',
            'status' => 'completed',
            'total' => $test->price,
        ]);

        $order->items()->create([
            'lab_test_id' => $test->id,
            'price' => $test->price,
            'result' => 'All normal.',
        ]);

        return $order;
    }

    public function test_patient_sees_their_lab_orders_on_profile(): void
    {
        $patient = $this->makeUser(['email' => 'patient@example.com']);
        $this->makeOrderForPatient($patient->id, $patient->email);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Lab Request')
            ->assertSee('Complete Blood Count');
    }

    public function test_patient_can_download_their_report(): void
    {
        Storage::fake('public');

        $patient = $this->makeUser(['email' => 'patient@example.com']);
        $order = $this->makeOrderForPatient($patient->id, $patient->email);
        $path = UploadedFile::fake()->create('result.pdf', 100, 'application/pdf')->store('lab_reports', 'public');

        $report = LabReport::create([
            'lab_order_id' => $order->id,
            'report_name' => 'Result',
            'file_path' => $path,
            'uploaded_by' => $this->makeAdmin()->id,
        ]);

        $this->actingAs($patient)
            ->get(route('profile.lab-reports.download', $report->id))
            ->assertOk();
    }

    public function test_patient_cannot_download_another_patients_report(): void
    {
        Storage::fake('public');

        $owner = $this->makeUser(['email' => 'owner@example.com']);
        $intruder = $this->makeUser(['email' => 'intruder@example.com']);
        $order = $this->makeOrderForPatient($owner->id, $owner->email);
        $path = UploadedFile::fake()->create('result.pdf', 100, 'application/pdf')->store('lab_reports', 'public');

        $report = LabReport::create([
            'lab_order_id' => $order->id,
            'report_name' => 'Result',
            'file_path' => $path,
            'uploaded_by' => $this->makeAdmin()->id,
        ]);

        $this->actingAs($intruder)
            ->get(route('profile.lab-reports.download', $report->id))
            ->assertForbidden();
    }

    public function test_patient_can_download_their_lab_order_report_pdf(): void
    {
        $patient = $this->makeUser(['email' => 'patient@example.com']);
        $order = $this->makeOrderForPatient($patient->id, $patient->email);

        $response = $this->actingAs($patient)
            ->get(route('profile.lab-orders.pdf', $order->id))
            ->assertOk();

        $this->assertStringContainsString('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_patient_cannot_download_another_patients_order_pdf(): void
    {
        $owner = $this->makeUser(['email' => 'owner@example.com']);
        $intruder = $this->makeUser(['email' => 'intruder@example.com']);
        $order = $this->makeOrderForPatient($owner->id, $owner->email);

        $this->actingAs($intruder)
            ->get(route('profile.lab-orders.pdf', $order->id))
            ->assertForbidden();
    }

    public function test_unverified_email_cannot_claim_guest_order(): void
    {
        Storage::fake('public');

        // Guest order: no user account, email only.
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest(['name' => 'Victim Panel XYZ']);
        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'user_id' => null,
            'email' => 'victim@example.com',
            'patient_name' => 'Victim Guest',
            'priority' => 'normal',
            'status' => 'completed',
            'total' => $test->price,
        ]);
        $order->items()->create([
            'lab_test_id' => $test->id,
            'price' => $test->price,
            'result' => 'All normal.',
        ]);
        $path = UploadedFile::fake()->create('result.pdf', 100, 'application/pdf')->store('lab_reports', 'public');
        $report = LabReport::create([
            'lab_order_id' => $order->id,
            'report_name' => 'Result',
            'file_path' => $path,
            'uploaded_by' => $this->makeAdmin()->id,
        ]);

        // Attacker changes profile email to the victim address but never verifies it.
        $attacker = $this->makeUser(['email' => 'attacker@example.com', 'email_verified_at' => null]);
        $attacker->update(['email' => 'victim@example.com', 'email_verified_at' => null]);

        $this->actingAs($attacker)
            ->get(route('profile'))
            ->assertOk()
            ->assertDontSee('Victim Panel XYZ');

        $this->actingAs($attacker)
            ->get(route('profile.lab-reports.download', $report->id))
            ->assertForbidden();

        $this->actingAs($attacker)
            ->get(route('profile.lab-orders.pdf', $order->id))
            ->assertForbidden();
    }

    public function test_verified_email_can_access_guest_order_via_email(): void
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest(['name' => 'Guest Panel ABC']);
        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'user_id' => null,
            'email' => 'guest@example.com',
            'patient_name' => 'Guest Patient',
            'priority' => 'normal',
            'status' => 'completed',
            'total' => $test->price,
        ]);
        $order->items()->create([
            'lab_test_id' => $test->id,
            'price' => $test->price,
            'result' => 'All normal.',
        ]);

        // Factory users are verified by default.
        $patient = $this->makeUser(['email' => 'guest@example.com']);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Guest Panel ABC');

        $this->actingAs($patient)
            ->get(route('profile.lab-orders.pdf', $order->id))
            ->assertOk();
    }

    public function test_guest_lab_orders_are_linked_to_user_on_login(): void
    {
        $doctor = $this->makeDoctor();
        $test = $this->makeLabTest();
        $order = LabOrder::create([
            'doctor_id' => $doctor->id,
            'user_id' => null,
            'email' => 'newbie@example.com',
            'patient_name' => 'New Patient',
            'priority' => 'normal',
            'status' => 'pending',
            'total' => $test->price,
        ]);

        $user = $this->makeUser(['email' => 'newbie@example.com', 'email_verified_at' => null]);

        GuestRecordLinker::link($user);

        $this->assertDatabaseHas('lab_orders', [
            'id' => $order->id,
            'user_id' => $user->id,
        ]);
    }
}
