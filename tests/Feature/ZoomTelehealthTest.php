<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use App\Modules\Zoom\Jobs\DeleteZoomMeeting;
use App\Modules\Zoom\Jobs\SyncZoomMeeting;
use App\Modules\Zoom\Mail\TelehealthReadyMail;
use App\Modules\Zoom\Models\ZoomMeeting;
use App\Modules\Zoom\Services\TelehealthJoinLink;
use App\Modules\Zoom\Services\ZoomException;
use App\Modules\Zoom\Services\ZoomMeetingSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ZoomTelehealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_telehealth_booking_requires_email_and_saves_its_mode(): void
    {
        Mail::fake();
        [, $doctor] = $this->doctor();
        $slot = TimeSlot::create(['time' => '09:00 AM', 'status' => 1]);
        $booking = [
            'doctor_id' => $doctor->id,
            'patient_name' => 'Telehealth Patient',
            'phone' => '+1 555 0100',
            'visit_type' => 1,
            'consultation_mode' => 'telehealth',
            'appointment_date' => now()->addDays(2)->toDateString(),
            'time_slot_id' => $slot->id,
            'gender' => 1,
        ];

        $this->post(route('appointment.store'), $booking)
            ->assertSessionHasErrors('email');

        $this->post(route('appointment.store'), $booking + ['email' => 'patient@example.test'])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'email' => 'patient@example.test',
            'consultation_mode' => 'telehealth',
        ]);
    }

    public function test_zoom_meetings_are_queued_when_telehealth_is_approved(): void
    {
        Queue::fake();
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor);

        $appointment->update(['status' => 1]);

        Queue::assertPushed(SyncZoomMeeting::class, fn ($job) => $job->appointmentId === $appointment->id);
    }

    public function test_approved_telehealth_meeting_uses_private_zoom_payload_and_emails_a_signed_join_link(): void
    {
        Cache::flush();
        Mail::fake();
        Queue::fake();
        $this->configureZoom();
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);
        Http::fake([
            'https://zoom.us/oauth/token*' => Http::response(['access_token' => 'zoom-api-token', 'expires_in' => 3600]),
            'https://api.zoom.us/v2/users/*/meetings' => Http::response([
                'id' => 123456789,
                'join_url' => 'https://zoom.us/j/123456789?pwd=private',
                'start_url' => 'https://zoom.us/s/123456789?zak=host-secret',
            ], 201),
        ]);

        app(ZoomMeetingSync::class)->sync($appointment->id);

        $meeting = ZoomMeeting::where('appointment_id', $appointment->id)->firstOrFail();
        $this->assertSame('ready', $meeting->sync_status);
        $this->assertSame('https://zoom.us/j/123456789?pwd=private', $meeting->join_url);
        $this->assertNotSame('https://zoom.us/j/123456789?pwd=private', $meeting->getRawOriginal('join_url'));
        Mail::assertQueued(TelehealthReadyMail::class, fn ($mail) => str_contains($mail->joinUrl, 'signature='));

        Http::assertSent(function ($request) use ($appointment) {
            $payload = $request->data();

            return str_contains($request->url(), '/users/me/meetings')
                && $request->method() === 'POST'
                && $payload['topic'] === 'Private telehealth consultation'
                && $payload['settings']['waiting_room'] === true
                && $payload['settings']['auto_recording'] === 'none'
                && ! str_contains(json_encode($payload), $appointment->patient_name)
                && ! str_contains(json_encode($payload), $appointment->email);
        });
    }

    public function test_sync_failure_is_recorded_as_a_safe_error_code(): void
    {
        Cache::flush();
        Queue::fake();
        $this->configureZoom();
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);
        Http::fake([
            'https://zoom.us/oauth/token*' => Http::response(['access_token' => 'zoom-api-token']),
            'https://api.zoom.us/v2/users/*/meetings' => Http::response(['message' => 'private upstream details'], 503),
        ]);

        try {
            app(ZoomMeetingSync::class)->sync($appointment->id);
            $this->fail('Expected Zoom API errors to be surfaced.');
        } catch (ZoomException $exception) {
            $this->assertSame('zoom_api_error_503', $exception->reason);
        }

        $this->assertDatabaseHas('zoom_meetings', [
            'appointment_id' => $appointment->id,
            'sync_status' => 'failed',
            'last_error_code' => 'zoom_api_error_503',
        ]);
    }

    public function test_appointment_changes_update_an_existing_zoom_meeting_without_losing_its_links(): void
    {
        Cache::flush();
        Queue::fake();
        Mail::fake();
        $this->configureZoom();
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);
        $meeting = ZoomMeeting::create([
            'appointment_id' => $appointment->id,
            'zoom_meeting_id' => '123456',
            'join_url' => 'https://zoom.us/j/123456?pwd=private',
            'start_url' => 'https://zoom.us/s/123456?zak=host-secret',
            'sync_status' => 'ready',
            'patient_notified_at' => now(),
        ]);
        Http::fake([
            'https://zoom.us/oauth/token*' => Http::response(['access_token' => 'zoom-api-token']),
            'https://api.zoom.us/v2/meetings/123456' => Http::response([], 204),
        ]);

        $appointment->update(['appointment_date' => now()->addDays(3)->toDateString()]);
        app(ZoomMeetingSync::class)->sync($appointment->id);

        $this->assertSame('ready', $meeting->fresh()->sync_status);
        $this->assertSame('https://zoom.us/j/123456?pwd=private', $meeting->fresh()->join_url);
        $this->assertNotNull($meeting->fresh()->patient_notified_at);
        Http::assertSent(fn ($request) => $request->method() === 'PATCH'
            && str_ends_with($request->url(), '/meetings/123456'));
        Mail::assertQueued(TelehealthReadyMail::class);
    }

    public function test_patient_join_link_is_signed_and_redirects_only_during_the_consultation_window(): void
    {
        Queue::fake();
        $start = now()->addMinutes(20);
        $appointment = $this->appointment(
            $this->doctor()[1],
            1,
            $start->toDateString(),
            $start->format('h:i A'),
        );
        ZoomMeeting::create([
            'appointment_id' => $appointment->id,
            'zoom_meeting_id' => '123456',
            'join_url' => 'https://zoom.us/j/123456?pwd=private',
            'start_url' => 'https://zoom.us/s/123456?zak=host-secret',
            'sync_status' => 'ready',
        ]);
        $url = app(TelehealthJoinLink::class)->for($appointment);

        $this->get(route('telehealth.join', $appointment))->assertForbidden();
        $this->get($url)->assertRedirect('https://zoom.us/j/123456?pwd=private');

        Queue::fake();
        $appointment->update(['status' => 3]);
        $this->get($url)->assertGone();
    }

    public function test_doctor_host_link_is_limited_to_the_assigned_doctor(): void
    {
        Queue::fake();
        $start = now()->addMinutes(20);
        [$doctorUser, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1, $start->toDateString(), $start->format('h:i A'));
        ZoomMeeting::create([
            'appointment_id' => $appointment->id,
            'zoom_meeting_id' => '123456',
            'join_url' => 'https://zoom.us/j/123456?pwd=private',
            'start_url' => 'https://zoom.us/s/123456?zak=host-secret',
            'sync_status' => 'ready',
        ]);
        [$otherDoctor] = $this->doctor();

        $this->actingAs($otherDoctor)
            ->get(route('doctor.telehealth.host', $appointment))
            ->assertNotFound();

        $this->actingAs($doctorUser)
            ->get(route('doctor.telehealth.host', $appointment))
            ->assertRedirect('https://zoom.us/s/123456?zak=host-secret');
    }

    public function test_cancellation_removes_the_remote_zoom_meeting(): void
    {
        Cache::flush();
        $this->configureZoom();
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 3);
        ZoomMeeting::create([
            'appointment_id' => $appointment->id,
            'zoom_meeting_id' => '123456',
            'join_url' => 'https://zoom.us/j/123456?pwd=private',
            'start_url' => 'https://zoom.us/s/123456?zak=host-secret',
            'sync_status' => 'ready',
        ]);
        Http::fake([
            'https://zoom.us/oauth/token*' => Http::response(['access_token' => 'zoom-api-token']),
            'https://api.zoom.us/v2/meetings/123456' => Http::response([], 204),
        ]);

        app(ZoomMeetingSync::class)->sync($appointment->id);

        $this->assertDatabaseMissing('zoom_meetings', ['appointment_id' => $appointment->id]);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE');
    }

    public function test_deleting_appointment_queues_zoom_meeting_cleanup(): void
    {
        Queue::fake();
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor);
        ZoomMeeting::create([
            'appointment_id' => $appointment->id,
            'zoom_meeting_id' => '123456',
            'join_url' => 'https://zoom.us/j/123456?pwd=private',
            'start_url' => 'https://zoom.us/s/123456?zak=host-secret',
            'sync_status' => 'ready',
        ]);

        $appointment->delete();

        Queue::assertPushed(DeleteZoomMeeting::class, fn ($job) => $job->zoomMeetingId === '123456');
    }

    public function test_zoom_module_can_be_disabled(): void
    {
        config()->set('modules.zoom', false);

        $this->get('/telehealth/1/join')->assertNotFound();
    }

    private function doctor(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Zoom Doctor',
            'slug' => 'zoom-doctor-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);

        return [$user, $doctor];
    }

    private function appointment(
        Doctor $doctor,
        int $status = 0,
        ?string $date = null,
        string $time = '09:00 AM',
    ): Appointment {
        $slot = TimeSlot::create(['time' => $time, 'status' => 1]);

        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Private Telehealth Patient',
            'email' => 'patient@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'telehealth',
            'appointment_date' => $date ?? now()->addDays(2)->toDateString(),
            'status' => $status,
        ]);
    }

    private function configureZoom(): void
    {
        config()->set('services.zoom.account_id', 'zoom-account-id');
        config()->set('services.zoom.client_id', 'zoom-client-id');
        config()->set('services.zoom.client_secret', 'zoom-client-secret');
    }
}
