<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\DoctorReview;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendTrustTest extends TestCase
{
    use RefreshDatabase;

    /** T7: blog detail shows the stored author plus the editorial role label. */
    public function test_blog_detail_shows_author_block_from_stored_data(): void
    {
        $blog = Blog::create([
            'title' => 'Trust Post',
            'slug' => 'trust-post-'.uniqid(),
            'author' => 'MediCare Team',
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'image' => 'blogs/blog-1.jpeg',
            'status' => 1,
        ]);

        $this->get(route('blog.show', $blog->slug))
            ->assertOk()
            ->assertSee('MediCare Team', false)
            ->assertSee(__('messages.trust.editorial_team'));
    }

    public function test_blog_detail_without_author_omits_the_block(): void
    {
        $blog = Blog::create([
            'title' => 'Anonymous Post',
            'slug' => 'anonymous-post-'.uniqid(),
            'author' => null,
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'image' => 'blogs/blog-1.jpeg',
            'status' => 1,
        ]);

        $this->get(route('blog.show', $blog->slug))
            ->assertOk()
            ->assertDontSee('mc-author-role', false);
    }

    /** T7: blog list keeps real author data, no invented fallback. */
    public function test_blog_list_uses_stored_author_without_unknown_fallback(): void
    {
        Blog::create([
            'title' => 'Listed Post',
            'slug' => 'listed-post-'.uniqid(),
            'author' => 'Dr. Ayesha Rahman',
            'excerpt' => 'Excerpt',
            'content' => '<p>Body</p>',
            'image' => 'blogs/blog-1.jpeg',
            'status' => 1,
        ]);

        $this->get(route('blog'))
            ->assertOk()
            ->assertSee(__('messages.trust.by_author', ['name' => 'Dr. Ayesha Rahman']))
            ->assertDontSee('Unknown');
    }

    /** T7: reviews born from a real appointment get the verified badge. */
    public function test_appointment_linked_review_shows_verified_patent_badge(): void
    {
        $doctor = Doctor::create([
            'name' => 'Verified Review Doctor',
            'slug' => 'verified-review-doctor-'.uniqid(),
            'status' => 1,
        ]);
        $patient = User::factory()->create(['role' => 'patient']);
        $appointment = $this->completedAppointment($doctor, $patient);

        DoctorReview::create([
            'doctor_id' => $doctor->id,
            'appointment_id' => $appointment->id,
            'user_id' => $patient->id,
            'rating' => 5,
            'comment' => 'Great care.',
            'status' => DoctorReview::STATUS_APPROVED,
        ]);

        $this->get(route('doctor.show', $doctor->id))
            ->assertOk()
            ->assertSee('mc-verified-badge', false)
            ->assertSee(__('messages.trust.verified_patient'))
            ->assertSee('Great care.');
    }

    /**
     * doctor_reviews.appointment_id is a required, unique FK and reviews are
     * only created by the verified-patient flow — so every public review
     * carries the verified marker.
     */
    public function test_every_public_review_carries_the_verified_marker(): void
    {
        $doctor = Doctor::create([
            'name' => 'Unverified Review Doctor',
            'slug' => 'unverified-review-doctor-'.uniqid(),
            'status' => 1,
        ]);

        $first = User::factory()->create(['role' => 'patient']);
        $second = User::factory()->create(['role' => 'patient']);

        $reviewOne = $this->completedAppointment($doctor, $first);
        $reviewTwo = $this->completedAppointment($doctor, $second);

        DoctorReview::create([
            'doctor_id' => $doctor->id,
            'appointment_id' => $reviewOne->id,
            'user_id' => $first->id,
            'rating' => 4,
            'comment' => 'Second opinion note.',
            'status' => DoctorReview::STATUS_APPROVED,
        ]);

        DoctorReview::create([
            'doctor_id' => $doctor->id,
            'appointment_id' => $reviewTwo->id,
            'user_id' => $second->id,
            'rating' => 5,
            'comment' => 'Calm and quick.',
            'status' => DoctorReview::STATUS_APPROVED,
        ]);

        $html = (string) $this->get(route('doctor.show', $doctor->id))->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'mc-verified-badge'));
        $this->assertStringContainsString('Second opinion note.', $html);
        $this->assertStringContainsString(__('messages.trust.verified_patient'), $html);
    }

    private function completedAppointment(Doctor $doctor, User $patient): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => TimeSlot::create(['time' => '10:00 AM', 'status' => 1])->id,
            'user_id' => $patient->id,
            'patient_name' => $patient->name,
            'email' => $patient->email,
            'phone' => '+8801711111111',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->subDay()->toDateString(),
            'status' => 2,
        ]);
    }
}
