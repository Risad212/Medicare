<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Prescription;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\GuestRecordLinker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRecordLinkerTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_match_is_case_insensitive_and_trimmed(): void
    {
        $doctor = $this->doctor();
        $user = User::factory()->create(['role' => 'patient', 'email' => 'test@x.com']);

        $appointment = $this->appointment($doctor, '  Test@X.com ');
        $invoice = $this->invoice('TEST@x.com');
        $prescription = $this->prescription($doctor, 'test@x.com ');

        GuestRecordLinker::link($user);

        $this->assertSame($user->id, $appointment->fresh()->user_id);
        $this->assertSame($user->id, $invoice->fresh()->user_id);
        $this->assertSame($user->id, $prescription->fresh()->patient_user_id);
    }

    public function test_non_matching_email_is_left_untouched(): void
    {
        $doctor = $this->doctor();
        $user = User::factory()->create(['role' => 'patient', 'email' => 'test@x.com']);
        $other = $this->appointment($doctor, 'someone-else@x.com');

        GuestRecordLinker::link($user);

        $this->assertNull($other->fresh()->user_id);
    }

    public function test_prescriptions_are_skipped_when_pharmacy_module_is_disabled(): void
    {
        config()->set('modules.pharmacy', false);
        $doctor = $this->doctor();
        $user = User::factory()->create(['role' => 'patient', 'email' => 'test@x.com']);
        $prescription = $this->prescription($doctor, 'test@x.com');

        GuestRecordLinker::link($user);

        $this->assertNull($prescription->fresh()->patient_user_id);
    }

    private function doctor(): Doctor
    {
        $user = User::factory()->create(['role' => 'doctor']);

        return Doctor::create([
            'name' => 'Linker Doctor',
            'slug' => 'linker-doctor-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);
    }

    private function appointment(Doctor $doctor, string $email): Appointment
    {
        $slot = TimeSlot::create(['time' => '11:00 AM', 'status' => 1]);

        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Guest Patient',
            'email' => $email,
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'status' => 0,
        ]);
    }

    private function prescription(Doctor $doctor, string $email): Prescription
    {
        return Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Guest Patient',
            'email' => $email,
            'diagnosis' => 'Checkup',
        ]);
    }

    private function invoice(string $email): Invoice
    {
        return Invoice::create([
            'invoice_no' => 'INV-'.uniqid(),
            'patient_name' => 'Guest Patient',
            'email' => $email,
        ]);
    }
}
