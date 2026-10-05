<?php

namespace Tests\Feature;

use App\Mail\AppointmentBookedMail;
use App\Mail\ContactFormMail;
use App\Models\Blog;
use App\Models\Doctor;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicFormSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_sends_a_message_and_returns_success(): void
    {
        Mail::fake();

        $this->from('/contact')->post('/contact-submit', [
            'name' => 'Public Visitor',
            'email' => 'visitor@example.test',
            'phone' => '01700000000',
            'subject' => 'Appointment question',
            'message' => 'Please tell me how to book.',
        ])->assertRedirect('/contact')->assertSessionHas('success', 'Message sent successfully.');

        Mail::assertSent(ContactFormMail::class, 1);
    }

    public function test_public_comment_is_saved_pending_moderation(): void
    {
        $blog = Blog::create([
            'title' => 'Commentable article',
            'slug' => 'commentable-article',
            'content' => '<p>Article content.</p>',
            'status' => 1,
        ]);

        $this->from('/blog/commentable-article')->post("/blog/{$blog->id}/comment", [
            'name' => 'Reader',
            'email' => 'reader@example.test',
            'comment' => 'Thank you for the helpful information.',
        ])->assertRedirect('/blog/commentable-article');

        $this->assertDatabaseHas('blog_comments', [
            'blog_id' => $blog->id,
            'name' => 'Reader',
            'email' => 'reader@example.test',
            'status' => 0,
        ]);
    }

    public function test_guest_can_book_an_open_appointment_slot(): void
    {
        Mail::fake();

        $doctor = Doctor::create([
            'name' => 'Dr Booking Test',
            'slug' => 'dr-booking-test',
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '11:30 AM', 'status' => 1]);

        $this->from('/appointment')->post('/appointment', [
            'doctor_id' => $doctor->id,
            'patient_name' => 'Booking Patient',
            'age' => 34,
            'phone' => '01700000001',
            'email' => 'booking@example.test',
            'visit_type' => 1,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'time_slot_id' => $slot->id,
            'gender' => 1,
        ])->assertRedirect('/appointment')->assertSessionHas('success', 'Appointment saved');

        $this->assertDatabaseHas('appointments', [
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Booking Patient',
            'status' => 0,
        ]);
        Mail::assertQueued(AppointmentBookedMail::class, 1);
    }

    public function test_guest_cannot_book_a_slot_outside_the_doctors_schedule(): void
    {
        $doctor = Doctor::create([
            'name' => 'Dr Schedule Test',
            'slug' => 'dr-schedule-test',
            'status' => 1,
        ]);
        $slot = TimeSlot::create(['time' => '11:30 AM', 'status' => 1]);
        $appointmentDate = now()->addDays(2)->toDateString();
        $closedWeekday = (now()->addDays(2)->dayOfWeek + 1) % 7;

        $doctor->schedules()->create([
            'weekday' => $closedWeekday,
            'time_slot_id' => $slot->id,
        ]);

        $this->from('/appointment')->post('/appointment', [
            'doctor_id' => $doctor->id,
            'patient_name' => 'Booking Patient',
            'phone' => '01700000001',
            'visit_type' => 1,
            'appointment_date' => $appointmentDate,
            'time_slot_id' => $slot->id,
            'gender' => 1,
        ])->assertRedirect('/appointment')
            ->assertSessionHasErrors('time_slot_id');

        $this->assertDatabaseMissing('appointments', [
            'doctor_id' => $doctor->id,
            'appointment_date' => $appointmentDate,
        ]);
    }
}
