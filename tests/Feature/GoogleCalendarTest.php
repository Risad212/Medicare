<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use App\Modules\GoogleCalendar\Jobs\SyncAppointmentToGoogleCalendar;
use App\Modules\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Modules\GoogleCalendar\Models\GoogleCalendarEvent;
use App\Modules\GoogleCalendar\Services\AppointmentCalendarSync;
use App\Modules\GoogleCalendar\Services\GoogleCalendarException;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GoogleCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_can_open_calendar_settings_and_admin_cannot(): void
    {
        [$doctorUser] = $this->doctor();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($doctorUser)
            ->get(route('doctor.calendar.index'))
            ->assertOk()
            ->assertSee('Connect Google Calendar');

        $this->actingAs($admin)
            ->get(route('doctor.calendar.index'))
            ->assertForbidden();
    }

    public function test_calendar_routes_are_disabled_with_the_module(): void
    {
        [$doctorUser] = $this->doctor();
        config()->set('modules.google_calendar', false);

        $this->actingAs($doctorUser)->get(route('doctor.calendar.index'))->assertNotFound();
    }

    public function test_connect_explains_when_google_oauth_is_not_configured(): void
    {
        [$doctorUser] = $this->doctor();
        config()->set('services.google.client_id', null);
        config()->set('services.google.client_secret', null);

        $this->actingAs($doctorUser)
            ->get(route('doctor.calendar.connect'))
            ->assertRedirect(route('doctor.calendar.index'))
            ->assertSessionHasErrors('calendar');
    }

    public function test_approved_appointments_sync_without_patient_information(): void
    {
        [$doctorUser, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);
        $connection = $this->connection($doctor);

        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events' => function ($request) {
                return Http::response(['id' => $request['id']], 201);
            },
        ]);

        app(AppointmentCalendarSync::class)->sync($appointment->id);

        $event = GoogleCalendarEvent::where('appointment_id', $appointment->id)->firstOrFail();
        $this->assertSame('synced', $event->sync_status);
        $this->assertNotEmpty($event->google_event_id);
        $this->assertSame($connection->id, $event->connection_id);
        $this->assertNotSame('access-token-secret', $connection->getRawOriginal('access_token'));
        $this->assertSame('access-token-secret', $connection->fresh()->access_token);

        Http::assertSent(function ($request) use ($appointment) {
            $body = $request->data();

            return $request->method() === 'POST'
                && $body['summary'] === 'Medical appointment'
                && $body['visibility'] === 'private'
                && ! str_contains(json_encode($body), $appointment->patient_name)
                && ! str_contains(json_encode($body), $appointment->email)
                && $body['start']['timeZone'] === config('app.timezone')
                && $body['end']['dateTime'] === Carbon::parse($appointment->appointment_date->format('Y-m-d').' 10:00 AM', config('app.timezone'))->toRfc3339String();
        });
    }

    public function test_pending_appointments_are_not_synced(): void
    {
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 0);
        $this->connection($doctor);
        Http::preventStrayRequests();

        app(AppointmentCalendarSync::class)->sync($appointment->id);

        $this->assertDatabaseMissing('google_calendar_events', ['appointment_id' => $appointment->id]);
    }

    public function test_cancelled_appointment_deletes_its_google_event(): void
    {
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 3);
        $connection = $this->connection($doctor);
        GoogleCalendarEvent::create([
            'appointment_id' => $appointment->id,
            'connection_id' => $connection->id,
            'google_event_id' => 'google-event-delete',
            'sync_status' => 'synced',
        ]);
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events/google-event-delete' => Http::response([], 204),
        ]);

        app(AppointmentCalendarSync::class)->sync($appointment->id);

        $this->assertDatabaseMissing('google_calendar_events', ['appointment_id' => $appointment->id]);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE');
    }

    public function test_calendar_sync_failures_are_recorded_with_a_safe_error_code(): void
    {
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);
        $this->connection($doctor);
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events' => Http::response(['error' => ['message' => 'private provider details']], 503),
        ]);

        try {
            app(AppointmentCalendarSync::class)->sync($appointment->id);
            $this->fail('Expected Google Calendar API errors to be reported.');
        } catch (GoogleCalendarException $exception) {
            $this->assertSame('google_api_error_503', $exception->reason);
            $this->assertSame('Google Calendar synchronization failed.', $exception->getMessage());
        }

        $this->assertDatabaseHas('google_calendar_events', [
            'appointment_id' => $appointment->id,
            'sync_status' => 'failed',
            'last_error_code' => 'google_api_error_503',
        ]);
    }

    public function test_expired_access_token_is_refreshed_after_google_rejects_it(): void
    {
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);
        $connection = $this->connection($doctor);
        $connection->update(['expires_at' => now()->subMinute()]);
        config()->set('services.google.client_id', 'oauth-client-id');
        config()->set('services.google.client_secret', 'oauth-client-secret');

        Http::fake([
            'https://www.googleapis.com/*' => function ($request) {
                if ($request->hasHeader('Authorization', 'Bearer access-token-secret')) {
                    return Http::response([], 401);
                }

                return Http::response(['id' => $request['id']], 201);
            },
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'refreshed-access-token',
                'expires_in' => 3600,
            ]),
        ]);

        app(AppointmentCalendarSync::class)->sync($appointment->id);

        $this->assertSame('refreshed-access-token', $connection->fresh()->access_token);
        $this->assertDatabaseHas('google_calendar_events', [
            'appointment_id' => $appointment->id,
            'sync_status' => 'synced',
        ]);
        Http::assertSent(fn ($request) => $request->url() === 'https://oauth2.googleapis.com/token'
            && $request['grant_type'] === 'refresh_token');
    }

    public function test_repeated_sync_updates_the_existing_google_event(): void
    {
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);
        $this->connection($doctor);
        Http::fake([
            'https://www.googleapis.com/calendar/v3/calendars/primary/events*' => function ($request) {
                $id = $request->method() === 'POST'
                    ? $request['id']
                    : basename(parse_url($request->url(), PHP_URL_PATH));

                return Http::response(['id' => $id], $request->method() === 'POST' ? 201 : 200);
            },
        ]);

        $sync = app(AppointmentCalendarSync::class);
        $sync->sync($appointment->id);
        Queue::fake();
        $appointment->update(['appointment_date' => now()->addDays(3)->toDateString()]);
        $sync->sync($appointment->id);

        $this->assertDatabaseCount('google_calendar_events', 1);
        Http::assertSentCount(2);
        Http::assertSent(fn ($request) => $request->method() === 'POST');
        Http::assertSent(fn ($request) => $request->method() === 'PATCH');
    }

    public function test_appointment_changes_queue_calendar_sync_after_commit(): void
    {
        Queue::fake();
        [, $doctor] = $this->doctor();
        $appointment = $this->appointment($doctor, 1);

        $appointment->update(['appointment_date' => now()->addDays(3)->toDateString()]);

        Queue::assertPushed(SyncAppointmentToGoogleCalendar::class, fn ($job) => $job->appointmentId === $appointment->id);
    }

    private function doctor(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Calendar Doctor',
            'slug' => 'calendar-doctor-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);

        return [$user, $doctor];
    }

    private function appointment(Doctor $doctor, int $status): Appointment
    {
        $slot = TimeSlot::create(['time' => '09:00 AM', 'status' => 1]);

        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'patient_name' => 'Protected Patient Name',
            'email' => 'patient@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'status' => $status,
        ]);
    }

    private function connection(Doctor $doctor): GoogleCalendarConnection
    {
        return GoogleCalendarConnection::create([
            'doctor_id' => $doctor->id,
            'google_email' => 'doctor@example.test',
            'access_token' => 'access-token-secret',
            'refresh_token' => 'refresh-token-secret',
            'expires_at' => now()->addHour(),
        ]);
    }
}
