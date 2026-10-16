<?php

namespace App\Modules\GoogleCalendar\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Modules\GoogleCalendar\Jobs\SyncAppointmentToGoogleCalendar;
use App\Modules\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Modules\GoogleCalendar\Models\GoogleCalendarEvent;
use App\Modules\GoogleCalendar\Services\GoogleCalendarClient;
use App\Modules\GoogleCalendar\Services\GoogleCalendarException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class CalendarConnectionController extends Controller
{
    public function index(): View
    {
        $doctor = $this->doctor();
        $connection = GoogleCalendarConnection::where('doctor_id', $doctor->id)->first();
        $failedEvents = $connection
            ? $connection->events()->where('sync_status', 'failed')->latest('updated_at')->limit(20)->get()
            : collect();

        return view('google-calendar.index', compact('connection', 'failedEvents'));
    }

    public function connect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route('doctor.calendar.index')
                ->withErrors(['calendar' => 'Google Calendar credentials are not configured.']);
        }

        return Socialite::driver('google')
            ->redirectUrl(config('services.google_calendar.redirect'))
            ->scopes(['https://www.googleapis.com/auth/calendar.events'])
            ->with([
                'access_type' => 'offline',
                'prompt' => 'consent',
                'include_granted_scopes' => 'true',
            ])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl(config('services.google_calendar.redirect'))
                ->user();
        } catch (Throwable $exception) {
            Log::warning('Google Calendar authorization failed.', [
                'user_id' => auth()->id(),
                'exception_class' => $exception::class,
            ]);

            return redirect()->route('doctor.calendar.index')
                ->withErrors(['calendar' => 'Google Calendar authorization failed. Please try again.']);
        }

        $email = $googleUser->getEmail();
        $accessToken = $googleUser->token;

        if (! is_string($email) || $email === '' || ! is_string($accessToken) || $accessToken === '') {
            return redirect()->route('doctor.calendar.index')
                ->withErrors(['calendar' => 'Google did not return the required account details.']);
        }

        $doctor = $this->doctor();
        $connection = GoogleCalendarConnection::firstOrNew(['doctor_id' => $doctor->id]);
        $refreshToken = $googleUser->refreshToken ?: $connection->refresh_token;

        if (! is_string($refreshToken) || $refreshToken === '') {
            return redirect()->route('doctor.calendar.index')
                ->withErrors(['calendar' => 'Google did not grant offline access. Reconnect and approve calendar access.']);
        }

        if ($connection->exists && $connection->google_email !== $email) {
            try {
                foreach ($connection->events()->get() as $event) {
                    app(GoogleCalendarClient::class)->deleteRemoteEvent($event);
                }
            } catch (GoogleCalendarException $exception) {
                Log::warning('Could not remove events from the previous Google Calendar account.', [
                    'doctor_id' => $doctor->id,
                    'error_code' => $exception->reason,
                ]);

                return redirect()->route('doctor.calendar.index')
                    ->withErrors(['calendar' => 'Could not finish switching calendar accounts. Please retry.']);
            }
        }

        $connection->fill([
            'google_email' => $email,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_at' => $googleUser->expiresIn ? now()->addSeconds($googleUser->expiresIn) : null,
        ])->save();

        Appointment::where('doctor_id', $doctor->id)
            ->where('status', 1)
            ->whereDate('appointment_date', '>=', today())
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($appointments): void {
                foreach ($appointments as $appointment) {
                    SyncAppointmentToGoogleCalendar::dispatch($appointment->id)->afterCommit();
                }
            });

        return redirect()->route('doctor.calendar.index')->with('success', 'Google Calendar connected.');
    }

    public function disconnect(GoogleCalendarClient $client): RedirectResponse
    {
        $doctor = $this->doctor();
        $connection = GoogleCalendarConnection::where('doctor_id', $doctor->id)->firstOrFail();

        try {
            foreach ($connection->events()->get() as $event) {
                $client->deleteRemoteEvent($event);
            }
        } catch (GoogleCalendarException $exception) {
            Log::warning('Google Calendar disconnect could not remove all linked events.', [
                'doctor_id' => $doctor->id,
                'error_code' => $exception->reason,
            ]);

            return redirect()->route('doctor.calendar.index')
                ->withErrors(['calendar' => 'Some calendar events could not be removed. The connection is still active; retry disconnect.']);
        }

        $revoked = false;
        try {
            $response = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/revoke', [
                'token' => $connection->refresh_token ?: $connection->access_token,
            ]);
            $revoked = $response->successful();
        } catch (ConnectionException) {
            Log::warning('Google Calendar access revocation could not be confirmed.', [
                'doctor_id' => $doctor->id,
            ]);
        }

        $connection->delete();

        if (! $revoked) {
            return redirect()->route('doctor.calendar.index')
                ->with('warning', 'Calendar disconnected locally. Google access revocation could not be confirmed; review connected apps in your Google account.');
        }

        return redirect()->route('doctor.calendar.index')->with('success', 'Google Calendar disconnected.');
    }

    public function retry(Appointment $appointment): RedirectResponse
    {
        $doctor = $this->doctor();
        abort_unless($appointment->doctor_id === $doctor->id, 404);

        $event = GoogleCalendarEvent::where('appointment_id', $appointment->id)
            ->where('sync_status', 'failed')
            ->firstOrFail();

        $event->update(['sync_status' => 'pending', 'last_error_code' => null]);
        SyncAppointmentToGoogleCalendar::dispatch($appointment->id)->afterCommit();

        return redirect()->route('doctor.calendar.index')->with('success', 'Calendar synchronization queued for retry.');
    }

    private function doctor(): Doctor
    {
        return Doctor::where('user_id', auth()->id())->firstOrFail();
    }
}
