<?php

namespace Tests\Feature;

use App\Mail\PrescriptionReadyMail;
use App\Models\Prescription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    private function prescriptionPayload(array $overrides = [], ?int $appointmentId = null): array
    {
        return array_merge([
            'appointment_id' => $appointmentId,
            'patient_name' => 'John Doe',
            'age' => 30,
            'gender' => 1,
            'phone' => '01711-223344',
            'email' => 'john@example.com',
            'symptoms' => 'Fever and a persistent cough.',
            'diagnosis' => 'Upper respiratory infection',
            'advice' => 'Plenty of fluids and rest.',
            'follow_up_date' => now()->addWeek()->toDateString(),
            'items' => [
                [
                    'medicine_name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'frequency' => '1-0-1',
                    'duration' => '5 days',
                    'quantity' => '10',
                    'instructions' => 'after meals',
                ],
                [
                    'medicine_name' => 'Vitamin C',
                    'dosage' => '250mg',
                    'frequency' => '0-0-1',
                    'duration' => '7 days',
                    'quantity' => '7',
                    'instructions' => 'before bed',
                ],
            ],
        ], $overrides);
    }

    private function makePrescription(array $overrides = []): Prescription
    {
        $doctor = $overrides['doctor_id'] ?? null;
        $doctor ??= $this->makeDoctor()->id;

        return Prescription::create(array_merge([
            'doctor_id' => $doctor,
            'patient_name' => 'Alice Patient',
            'age' => 28,
            'gender' => 2,
            'phone' => '01712-222222',
            'email' => 'alice@example.com',
            'diagnosis' => 'Lower respiratory infection',
        ], $overrides));
    }

    public function test_doctor_can_view_own_prescriptions_index(): void
    {
        $doctor = $this->makeDoctor();
        $this->makePrescription(['doctor_id' => $doctor->id, 'patient_name' => 'Visible Patient']);

        $this->actingAs($doctor->user)
            ->get(route('doctor.prescriptions.index'))
            ->assertOk()
            ->assertSee('Visible Patient');
    }

    public function test_doctor_can_create_prescription_from_appointment(): void
    {
        Mail::fake();

        $doctor = $this->makeDoctor();
        $patient = $this->makeUser(['email' => 'alice@example.com']);
        $appointment = $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'user_id' => $patient->id,
            'patient_name' => 'Alice Patient',
            'age' => 28,
            'gender' => 2,
            'phone' => '01712-222222',
            'email' => 'alice@example.com',
            'status' => 1,
        ]);

        $response = $this->actingAs($doctor->user)
            ->post(route('doctor.prescriptions.store'), $this->prescriptionPayload([], $appointment->id));

        $response->assertRedirect(route('doctor.prescriptions.show', 1));

        $this->assertDatabaseHas('prescriptions', [
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'patient_user_id' => $patient->id,
            'patient_name' => 'Alice Patient',
            'age' => 28,
            'gender' => 2,
            'email' => 'alice@example.com',
            'diagnosis' => 'Upper respiratory infection',
        ]);

        $this->assertDatabaseCount('prescription_items', 2);

        Mail::assertQueued(PrescriptionReadyMail::class, function ($mail) {
            return $mail->hasTo('alice@example.com');
        });
    }

    public function test_doctor_can_create_walkin_prescription_without_email(): void
    {
        Mail::fake();

        $doctor = $this->makeDoctor();

        $payload = $this->prescriptionPayload([
            'appointment_id' => null,
            'email' => null,
            'patient_name' => 'Walk-in Patient',
        ]);

        $this->actingAs($doctor->user)
            ->post(route('doctor.prescriptions.store'), $payload)
            ->assertRedirect();

        $this->assertDatabaseHas('prescriptions', [
            'doctor_id' => $doctor->id,
            'appointment_id' => null,
            'patient_user_id' => null,
            'patient_name' => 'Walk-in Patient',
            'email' => null,
        ]);

        $this->assertDatabaseCount('prescription_items', 2);

        Mail::assertNothingSent();
    }

    public function test_creating_prescription_requires_diagnosis_and_items(): void
    {
        $doctor = $this->makeDoctor();

        $this->actingAs($doctor->user)
            ->from(route('doctor.prescriptions.create'))
            ->post(route('doctor.prescriptions.store'), [
                'appointment_id' => null,
                'patient_name' => 'John Doe',
                'diagnosis' => '',
                'items' => [],
            ])
            ->assertSessionHasErrors(['diagnosis', 'items']);

        $this->assertDatabaseCount('prescriptions', 0);
    }

    public function test_doctor_cannot_use_another_doctors_appointment(): void
    {
        $doctorA = $this->makeDoctor();
        $doctorB = $this->makeDoctor();
        $appointment = $this->makeAppointment(['doctor_id' => $doctorA->id, 'status' => 1]);

        $this->actingAs($doctorB->user)
            ->post(route('doctor.prescriptions.store'), $this->prescriptionPayload([], $appointment->id))
            ->assertForbidden();

        $this->assertDatabaseCount('prescriptions', 0);
    }

    public function test_doctor_cannot_view_another_doctors_prescription(): void
    {
        $doctorA = $this->makeDoctor();
        $doctorB = $this->makeDoctor();
        $prescription = $this->makePrescription(['doctor_id' => $doctorA->id]);

        $this->actingAs($doctorB->user)
            ->get(route('doctor.prescriptions.show', $prescription))
            ->assertForbidden();
    }

    public function test_doctor_can_update_prescription_and_items_are_replaced(): void
    {
        $doctor = $this->makeDoctor();
        $prescription = $this->makePrescription(['doctor_id' => $doctor->id]);

        $payload = $this->prescriptionPayload([
            'appointment_id' => null,
            'diagnosis' => 'Updated diagnosis',
            'items' => [
                ['medicine_name' => 'Ibuprofen', 'dosage' => '400mg', 'frequency' => '1-0-1', 'duration' => '3 days'],
            ],
        ]);

        $this->actingAs($doctor->user)
            ->put(route('doctor.prescriptions.update', $prescription), $payload)
            ->assertRedirect(route('doctor.prescriptions.show', $prescription));

        $this->assertDatabaseHas('prescriptions', [
            'id' => $prescription->id,
            'diagnosis' => 'Updated diagnosis',
        ]);

        $this->assertSame(1, $prescription->fresh()->items()->count());
        $this->assertDatabaseMissing('prescription_items', [
            'medicine_name' => 'Paracetamol',
        ]);
    }

    public function test_doctor_can_delete_own_prescription(): void
    {
        $doctor = $this->makeDoctor();
        $prescription = $this->makePrescription(['doctor_id' => $doctor->id]);

        $this->actingAs($doctor->user)
            ->delete(route('doctor.prescriptions.destroy', $prescription))
            ->assertRedirect(route('doctor.prescriptions.index'));

        $this->assertDatabaseMissing('prescriptions', ['id' => $prescription->id]);
        $this->assertDatabaseMissing('prescription_items', ['prescription_id' => $prescription->id]);
    }

    public function test_doctor_can_download_own_prescription_pdf(): void
    {
        $doctor = $this->makeDoctor();
        $prescription = $this->makePrescription(['doctor_id' => $doctor->id]);

        $response = $this->actingAs($doctor->user)
            ->get(route('doctor.prescriptions.pdf', $prescription));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type') ?? '');
    }

    public function test_doctor_cannot_download_another_doctors_prescription_pdf(): void
    {
        $doctorA = $this->makeDoctor();
        $doctorB = $this->makeDoctor();
        $prescription = $this->makePrescription(['doctor_id' => $doctorA->id]);

        $this->actingAs($doctorB->user)
            ->get(route('doctor.prescriptions.pdf', $prescription))
            ->assertForbidden();
    }

    public function test_patient_profile_lists_linked_prescriptions(): void
    {
        $patient = $this->makeUser();
        $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
            'patient_user_id' => $patient->id,
            'patient_name' => 'Linked Prescription Patient',
            'email' => 'other@example.com',
        ]);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Linked Prescription Patient');
    }

    public function test_patient_profile_lists_prescriptions_by_verified_email(): void
    {
        $patient = $this->makeUser(['email_verified_at' => now()]);
        $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
            'patient_user_id' => null,
            'email' => $patient->email,
            'patient_name' => 'Email Matched Prescription',
        ]);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertSee('Email Matched Prescription');
    }

    public function test_patient_can_download_own_prescription_pdf(): void
    {
        $patient = $this->makeUser();
        $prescription = $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
            'patient_user_id' => $patient->id,
        ]);

        $response = $this->actingAs($patient)
            ->get(route('profile.prescriptions.pdf', $prescription));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type') ?? '');
    }

    public function test_patient_cannot_download_another_patients_prescription_pdf(): void
    {
        $patientA = $this->makeUser();
        $patientB = $this->makeUser();
        $prescription = $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
            'patient_user_id' => $patientA->id,
        ]);

        $this->actingAs($patientB)
            ->get(route('profile.prescriptions.pdf', $prescription))
            ->assertForbidden();
    }

    public function test_walkin_prescription_not_visible_to_unrelated_patient(): void
    {
        $patient = $this->makeUser();
        $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
            'patient_user_id' => null,
            'email' => null,
            'patient_name' => 'Anonymous Walk-in',
        ]);

        $this->actingAs($patient)
            ->get(route('profile'))
            ->assertOk()
            ->assertDontSee('Anonymous Walk-in');
    }

    public function test_admin_can_view_prescriptions_index(): void
    {
        $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
            'patient_name' => 'Admin Visible Patient',
        ]);

        $this->actingAs($this->makeAdmin())
            ->get(route('admin.prescriptions.index'))
            ->assertOk()
            ->assertSee('Admin Visible Patient');
    }

    public function test_admin_can_delete_prescription(): void
    {
        $prescription = $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
        ]);

        $this->actingAs($this->makeAdmin())
            ->delete(route('admin.prescriptions.destroy', $prescription))
            ->assertRedirect();

        $this->assertDatabaseMissing('prescriptions', ['id' => $prescription->id]);
    }

    public function test_admin_can_download_prescription_pdf(): void
    {
        $prescription = $this->makePrescription([
            'doctor_id' => $this->makeDoctor()->id,
        ]);

        $response = $this->actingAs($this->makeAdmin())
            ->get(route('admin.prescriptions.pdf', $prescription));

        $response->assertOk();
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type') ?? '');
    }
}
