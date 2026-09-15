<?php

namespace Tests\Feature;

use App\Mail\AppointmentBookedMail;
use App\Models\Appointment;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Concerns\CreatesModels;
use Tests\TestCase;

class AppointmentBookingTest extends TestCase
{
    use CreatesModels;
    use RefreshDatabase;

    public function test_guest_can_book_an_appointment(): void
    {
        $this->from(route('appointment'))->post(route('appointment.store'), $this->appointmentPayload())
            ->assertRedirect()
            ->assertSessionHas('success', 'Appointment saved');

        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'John Doe',
            'phone' => '01711-223344',
            'visit_type' => 2,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 0,
        ]);

        $appointment = Appointment::first();
        $this->assertNull($appointment->user_id);
        $this->assertNotNull($appointment->cancellation_token);
        $this->assertEquals(40, strlen($appointment->cancellation_token));
    }

    public function test_booking_sends_confirmation_email_when_email_provided(): void
    {
        Mail::fake();

        $this->from(route('appointment'))->post(route('appointment.store'), $this->appointmentPayload())
            ->assertRedirect();

        Mail::assertQueued(AppointmentBookedMail::class);
    }

    public function test_booking_does_not_send_email_without_email(): void
    {
        Mail::fake();

        $this->from(route('appointment'))->post(route('appointment.store'), $this->appointmentPayload([
            'email' => null,
        ]))->assertRedirect();

        Mail::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_booking_links_to_logged_in_patient(): void
    {
        $patient = $this->makeUser();

        $this->actingAs($patient)
            ->from(route('appointment'))
            ->post(route('appointment.store'), $this->appointmentPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'patient_name' => 'John Doe',
            'user_id' => $patient->id,
        ]);
    }

    public function test_booking_validates_required_fields(): void
    {
        $this->from(route('appointment'))->post(route('appointment.store'), [])
            ->assertSessionHasErrors([
                'doctor_id', 'patient_name', 'phone', 'visit_type', 'appointment_date', 'time_slot_id', 'gender',
            ]);
    }

    public function test_booking_rejects_invalid_phone_visit_type_and_gender(): void
    {
        $payload = $this->appointmentPayload([
            'phone' => 'abc$$$',
            'visit_type' => 9,
            'gender' => 9,
        ]);

        $this->from(route('appointment'))->post(route('appointment.store'), $payload)
            ->assertSessionHasErrors(['phone', 'visit_type', 'gender']);
    }

    public function test_booking_cannot_select_inactive_doctor_or_slot(): void
    {
        $doctor = $this->makeDoctor(['status' => 0]);
        $slot = $this->makeTimeSlot(['status' => 0]);

        $this->from(route('appointment'))->post(route('appointment.store'), $this->appointmentPayload([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
        ]))->assertNotFound();
    }

    public function test_booking_rejects_double_booking_same_slot(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => now()->addDay()->toDateString(),
        ]);

        $this->from(route('appointment'))->post(route('appointment.store'), $this->appointmentPayload([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
        ]))->assertSessionHasErrors('time_slot_id');
    }

    public function test_booking_allows_same_slot_after_cancellation(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'status' => 3,
        ]);

        $this->from(route('appointment'))->post(route('appointment.store'), $this->appointmentPayload([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
        ]))->assertRedirect()->assertSessionHas('success', 'Appointment saved');
    }

    public function test_database_rejects_duplicate_active_booking_same_slot(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'status' => 0,
        ]);

        $this->assertThrows(
            fn () => $this->makeAppointment([
                'doctor_id' => $doctor->id,
                'time_slot_id' => $slot->id,
                'appointment_date' => $date,
                'status' => 0,
            ]),
            QueryException::class
        );
    }

    public function test_database_allows_active_booking_after_cancelled_one(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'status' => 3,
        ]);

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'status' => 0,
        ]);

        $this->assertDatabaseCount('appointments', 2);
    }

    public function test_database_allows_second_cancel_of_a_rebooked_slot(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot();
        $date = now()->addDay()->toDateString();

        // A cancelled booking for the slot...
        $first = $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'status' => 3,
        ]);

        // ...then a re-booking of the same slot...
        $second = $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => $date,
            'status' => 0,
        ]);

        // ...then cancelling the re-booking must not hit the unique index:
        // two cancelled rows for the same slot must coexist (NULL booking_active).
        try {
            $second->update(['status' => 3]);
            $cancelWorked = true;
        } catch (QueryException $e) {
            $cancelWorked = false;
        }

        $this->assertTrue($cancelWorked, 'Second cancel of a re-booked slot must not hit a unique-constraint error.');
        $this->assertSame(3, $second->refresh()->status);
        $this->assertDatabaseCount('appointments', 2);
    }

    public function test_available_slots_json_returns_all_active_slots(): void
    {
        $doctor = $this->makeDoctor();
        $this->makeTimeSlot(['time' => '09:00 AM']);
        $this->makeTimeSlot(['time' => '02:00 PM']);
        $this->makeTimeSlot(['time' => '11:00 PM', 'status' => 0]);

        $response = $this->getJson(route('get.slots', ['doctor_id' => $doctor->id, 'date' => now()->addDay()->toDateString()]))
            ->assertOk()
            ->assertJsonStructure(['slots', 'bookedSlotIds']);

        $this->assertCount(2, $response->json('slots'));
    }

    public function test_available_slots_marks_booked_slots(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot(['time' => '10:00 AM']);

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => now()->addDay()->toDateString(),
        ]);

        $response = $this->getJson(route('get.slots', ['doctor_id' => $doctor->id, 'date' => now()->addDay()->toDateString()]))
            ->assertOk();

        $this->assertTrue(in_array($slot->id, $response->json('bookedSlotIds')));
    }

    public function test_available_slots_ignores_cancelled_bookings(): void
    {
        $doctor = $this->makeDoctor();
        $slot = $this->makeTimeSlot(['time' => '10:00 AM']);

        $this->makeAppointment([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 3,
        ]);

        $response = $this->getJson(route('get.slots', ['doctor_id' => $doctor->id, 'date' => now()->addDay()->toDateString()]))
            ->assertOk();

        $this->assertEmpty($response->json('bookedSlotIds'));
    }

    public function test_appointment_form_submits_numeric_visit_types(): void
    {
        $this->makeDoctor();

        $response = $this->get(route('appointment'))->assertOk();

        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/value="1"[^>]*>\s*First Visit/', $html);
        $this->assertMatchesRegularExpression('/value="2"[^>]*>\s*Second Visit/', $html);
        $this->assertMatchesRegularExpression('/value="3"[^>]*>\s*Report Review/', $html);
        // The old string-label options are gone — they made every booking fail `in:1,2,3`.
        $this->assertStringNotContainsString('value="First Visit"', $html);

        // The date field must repopulate under its own name after validation errors.
        $this->assertMatchesRegularExpression('/id="appointment_date"[^>]*value="/', $html);
    }
}
