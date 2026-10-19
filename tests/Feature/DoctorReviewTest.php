<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorReview;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorReviewTest extends TestCase
{
    use RefreshDatabase;

    private function patient(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'patient']);
    }

    private function doctor(): Doctor
    {
        $user = User::factory()->create(['role' => 'doctor']);

        return Doctor::create([
            'name' => 'Dr. Reviewed',
            'slug' => 'dr-reviewed-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);
    }

    private function appointment(User $patient, int $status = 2, ?Doctor $doctor = null): Appointment
    {
        return Appointment::create([
            'doctor_id' => ($doctor ?? $this->doctor())->id,
            'time_slot_id' => TimeSlot::create(['time' => '10:00 AM', 'status' => 1])->id,
            'user_id' => $patient->id,
            'patient_name' => $patient->name,
            'email' => $patient->email,
            'phone' => '+8801711111111',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->subDay()->toDateString(),
            'status' => $status,
        ]);
    }

    private function review(User $patient, Appointment $appointment, array $attributes = []): DoctorReview
    {
        return DoctorReview::create($attributes + [
            'doctor_id' => $appointment->doctor_id,
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'rating' => 5,
            'comment' => 'Wonderful consultation.',
            'status' => DoctorReview::STATUS_PENDING,
        ]);
    }

    public function test_patient_submits_review_for_own_completed_appointment(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);

        $this->actingAs($patient)
            ->from(route('profile'))
            ->post(route('profile.appointments.review', $appointment->id), [
                'rating' => 4,
                'comment' => 'Quick and caring.',
            ])
            ->assertRedirect(route('profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('doctor_reviews', [
            'appointment_id' => $appointment->id,
            'rating' => 4,
            'status' => DoctorReview::STATUS_PENDING,
        ]);
    }

    public function test_review_requires_completed_appointment(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient, status: 1);

        $this->actingAs($patient)
            ->from(route('profile'))
            ->post(route('profile.appointments.review', $appointment->id), ['rating' => 5])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('doctor_reviews', 0);
    }

    public function test_patient_cannot_review_another_patients_appointment(): void
    {
        $owner = $this->patient();
        $intruder = $this->patient();
        $appointment = $this->appointment($owner);

        $this->actingAs($intruder)
            ->from(route('profile'))
            ->post(route('profile.appointments.review', $appointment->id), ['rating' => 1])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('doctor_reviews', 0);
    }

    public function test_duplicate_review_is_rejected(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);
        $this->review($patient, $appointment);

        $this->actingAs($patient)
            ->from(route('profile'))
            ->post(route('profile.appointments.review', $appointment->id), ['rating' => 5])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('doctor_reviews', 1);
    }

    public function test_rating_out_of_range_is_rejected(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);

        $this->actingAs($patient)
            ->from(route('profile'))
            ->post(route('profile.appointments.review', $appointment->id), ['rating' => 6])
            ->assertSessionHasErrors('rating');

        $this->assertDatabaseCount('doctor_reviews', 0);
    }

    public function test_guest_cannot_submit_review(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);

        $this->post(route('profile.appointments.review', $appointment->id), ['rating' => 5])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('doctor_reviews', 0);
    }

    public function test_only_approved_reviews_appear_on_doctor_page(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);
        $pending = $this->review($patient, $appointment, ['comment' => 'Awaiting moderation here.']);

        $this->get(route('doctor.show', $appointment->doctor_id))
            ->assertOk()
            ->assertDontSee('Awaiting moderation here.');

        $pending->update(['status' => DoctorReview::STATUS_APPROVED]);

        $this->get(route('doctor.show', $appointment->doctor_id))
            ->assertOk()
            ->assertSee('Awaiting moderation here.');
    }

    public function test_admin_moderates_review_and_patient_cannot(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);
        $review = $this->review($patient, $appointment);

        $this->actingAs($patient)
            ->patch(route('admin.doctor-reviews.update', $review->id), ['status' => 1])
            ->assertForbidden();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.doctor-reviews.index'))
            ->assertOk();

        $this->actingAs($admin)
            ->patch(route('admin.doctor-reviews.update', $review->id), ['status' => 1])
            ->assertRedirect(route('admin.doctor-reviews.index'));

        $this->assertSame(DoctorReview::STATUS_APPROVED, $review->fresh()->status);

        $this->actingAs($admin)
            ->delete(route('admin.doctor-reviews.destroy', $review->id))
            ->assertRedirect();

        $this->assertDatabaseCount('doctor_reviews', 0);
    }

    public function test_doctor_role_cannot_moderate_reviews(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);
        $review = $this->review($patient, $appointment);
        $doctorUser = User::factory()->create(['role' => 'doctor']);

        $this->actingAs($doctorUser)
            ->get(route('admin.doctor-reviews.index'))
            ->assertForbidden();

        $this->actingAs($doctorUser)
            ->patch(route('admin.doctor-reviews.update', $review->id), ['status' => 1])
            ->assertForbidden();
    }

    public function test_disabled_module_hides_review_routes_and_ui(): void
    {
        config(['modules.reviews' => false]);

        $patient = $this->patient();
        $appointment = $this->appointment($patient);

        $this->actingAs($patient)
            ->post(route('profile.appointments.review', $appointment->id), ['rating' => 5])
            ->assertNotFound();

        $response = $this->get(route('doctor.show', $appointment->doctor_id));
        $response->assertOk()->assertDontSee(__('messages.reviews.heading'));
    }

    public function test_doctor_page_shows_rating_summary_of_approved_reviews(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);
        $this->review($patient, $appointment, ['rating' => 4, 'status' => DoctorReview::STATUS_APPROVED]);

        $this->get(route('doctor.show', $appointment->doctor_id))
            ->assertOk()
            ->assertSee(__('messages.reviews.rating_of', ['avg' => 4.0]))
            ->assertSee(__('messages.reviews.based_on', ['count' => 1]));
    }
}
