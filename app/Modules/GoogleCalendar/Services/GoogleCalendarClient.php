<?php

namespace App\Modules\GoogleCalendar\Services;

use App\Models\Appointment;
use App\Modules\GoogleCalendar\Models\GoogleCalendarConnection;
use App\Modules\GoogleCalendar\Models\GoogleCalendarEvent;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GoogleCalendarClient
{
    private const API = 'https://www.googleapis.com/calendar/v3/calendars/primary/events';

    public function sync(Appointment $appointment, GoogleCalendarConnection $connection): GoogleCalendarEvent
    {
        $event = GoogleCalendarEvent::firstOrNew(['appointment_id' => $appointment->id]);

        if ($event->exists && $event->connection_id !== $connection->id) {
            $this->deleteRemoteEvent($event);
            $event->delete();
            $event = new GoogleCalendarEvent(['appointment_id' => $appointment->id]);
        }

        $event->fill([
            'connection_id' => $connection->id,
            'sync_status' => 'pending',
            'last_error_code' => null,
        ]);
        $event->save();

        try {
            $payload = $this->payload($appointment);
            $eventId = $event->google_event_id;

            if ($eventId) {
                $eventId = $this->updateEvent($connection, $eventId, $payload);
            }

            if (! $eventId) {
                $eventId = str_replace('-', '', Str::lower((string) Str::uuid()));
                $event->update(['google_event_id' => $eventId]);
                $this->createEvent($connection, $eventId, $payload);
            }

            $event->update([
                'google_event_id' => $eventId,
                'sync_status' => 'synced',
                'last_error_code' => null,
                'synced_at' => now(),
            ]);

            return $event->refresh();
        } catch (GoogleCalendarException $exception) {
            $event->update([
                'sync_status' => 'failed',
                'last_error_code' => $exception->reason,
            ]);

            throw $exception;
        }
    }

    public function deleteRemoteEvent(GoogleCalendarEvent $event): void
    {
        if (! $event->google_event_id || ! $event->connection) {
            $event->delete();

            return;
        }

        $response = $this->authorizedRequest(
            $event->connection,
            'DELETE',
            self::API.'/'.rawurlencode($event->google_event_id),
        );

        if (! $response->successful() && $response->status() !== 404) {
            throw $this->apiException($response);
        }

        $event->delete();
    }

    private function payload(Appointment $appointment): array
    {
        if (! $appointment->timeSlot || ! is_string($appointment->timeSlot->time)) {
            throw new GoogleCalendarException('appointment_time_unavailable');
        }

        $start = Carbon::createFromFormat(
            'Y-m-d h:i A',
            $appointment->appointment_date->format('Y-m-d').' '.$appointment->timeSlot->time,
            config('app.timezone'),
        );
        $duration = max(5, min(1440, (int) config('services.google_calendar.event_duration_minutes', 60)));
        $end = $start->copy()->addMinutes($duration);
        $timezone = config('app.timezone');

        return [
            'summary' => 'Medical appointment',
            'visibility' => 'private',
            'start' => ['dateTime' => $start->toRfc3339String(), 'timeZone' => $timezone],
            'end' => ['dateTime' => $end->toRfc3339String(), 'timeZone' => $timezone],
        ];
    }

    private function createEvent(
        GoogleCalendarConnection $connection,
        string $eventId,
        array $payload,
    ): void {
        $payload['id'] = $eventId;
        $response = $this->authorizedRequest($connection, 'POST', self::API, ['json' => $payload]);

        if (! $response->successful() || $response->json('id') !== $eventId) {
            throw $this->apiException($response);
        }
    }

    private function updateEvent(
        GoogleCalendarConnection $connection,
        string $eventId,
        array $payload,
    ): ?string {
        $response = $this->authorizedRequest(
            $connection,
            'PATCH',
            self::API.'/'.rawurlencode($eventId),
            ['json' => $payload],
        );

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful() || ! is_string($response->json('id'))) {
            throw $this->apiException($response);
        }

        return $response->json('id');
    }

    private function authorizedRequest(
        GoogleCalendarConnection $connection,
        string $method,
        string $url,
        array $options = [],
    ): Response {
        $response = $this->send($connection->access_token, $method, $url, $options);

        if ($response->status() === 401 && ! $connection->refresh_token) {
            throw new GoogleCalendarException('reauthorization_required');
        }

        if ($response->status() === 401) {
            $response = $this->send($this->refreshAccessToken($connection), $method, $url, $options);

            if ($response->status() === 401) {
                throw new GoogleCalendarException('reauthorization_required');
            }
        }

        return $response;
    }

    private function send(string $token, string $method, string $url, array $options = []): Response
    {
        try {
            return Http::acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->send($method, $url, $options);
        } catch (ConnectionException $exception) {
            throw new GoogleCalendarException('network_error', $exception);
        }
    }

    private function refreshAccessToken(GoogleCalendarConnection $connection): string
    {
        $refreshToken = $connection->refresh_token;
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (! $refreshToken || ! $clientId || ! $clientSecret) {
            throw new GoogleCalendarException('reauthorization_required');
        }

        try {
            $response = Http::asForm()
                ->acceptJson()
                ->timeout(10)
                ->post('https://oauth2.googleapis.com/token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ]);
        } catch (ConnectionException $exception) {
            throw new GoogleCalendarException('network_error', $exception);
        }

        $accessToken = $response->json('access_token');

        if (! $response->successful() || ! is_string($accessToken) || $accessToken === '') {
            throw new GoogleCalendarException('reauthorization_required');
        }

        $connection->update([
            'access_token' => $accessToken,
            'expires_at' => now()->addSeconds((int) $response->json('expires_in', 3600)),
        ]);

        return $accessToken;
    }

    private function apiException(Response $response): GoogleCalendarException
    {
        return new GoogleCalendarException('google_api_error_'.$response->status());
    }
}
