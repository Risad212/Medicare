<?php

namespace App\Modules\Zoom\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ZoomClient
{
    private const API = 'https://api.zoom.us/v2';

    public function createMeeting(Appointment $appointment): array
    {
        $response = $this->request('POST', '/users/'.rawurlencode(config('services.zoom.host_user_id', 'me')).'/meetings', [
            'json' => $this->payload($appointment),
        ]);

        if (! $response->successful()) {
            throw $this->apiException($response);
        }

        $meetingId = $response->json('id');
        $joinUrl = $response->json('join_url');
        $startUrl = $response->json('start_url');

        if (! $meetingId || ! is_string($joinUrl) || ! is_string($startUrl)) {
            throw new ZoomException('invalid_zoom_response');
        }
        $this->validateZoomUrl($joinUrl);
        $this->validateZoomUrl($startUrl);

        return [
            'zoom_meeting_id' => (string) $meetingId,
            'join_url' => $joinUrl,
            'start_url' => $startUrl,
        ];
    }

    public function updateMeeting(string $meetingId, Appointment $appointment): ?array
    {
        $response = $this->request('PATCH', '/meetings/'.rawurlencode($meetingId), [
            'json' => $this->payload($appointment),
        ]);

        if ($response->status() === 404) {
            return null;
        }

        if (! $response->successful()) {
            throw $this->apiException($response);
        }

        $joinUrl = $response->json('join_url');
        $startUrl = $response->json('start_url');

        if ($joinUrl !== null) {
            $this->validateZoomUrl($joinUrl);
        }
        if ($startUrl !== null) {
            $this->validateZoomUrl($startUrl);
        }

        return [
            'zoom_meeting_id' => $meetingId,
            'join_url' => $joinUrl,
            'start_url' => $startUrl,
        ];
    }

    public function deleteMeeting(string $meetingId): void
    {
        $response = $this->request('DELETE', '/meetings/'.rawurlencode($meetingId));

        if (! $response->successful() && $response->status() !== 404) {
            throw $this->apiException($response);
        }
    }

    private function payload(Appointment $appointment): array
    {
        if (! $appointment->timeSlot || ! is_string($appointment->timeSlot->time)) {
            throw new ZoomException('appointment_time_unavailable');
        }

        $start = Carbon::createFromFormat(
            'Y-m-d h:i A',
            $appointment->appointment_date->format('Y-m-d').' '.$appointment->timeSlot->time,
            config('app.timezone'),
        );

        return [
            'topic' => 'Private telehealth consultation',
            'type' => 2,
            'start_time' => $start->toRfc3339String(),
            'duration' => max(5, min(1440, (int) config('services.zoom.duration_minutes', 60))),
            'timezone' => config('app.timezone'),
            'settings' => [
                'join_before_host' => false,
                'waiting_room' => true,
                'auto_recording' => 'none',
            ],
        ];
    }

    private function request(string $method, string $path, array $options = []): Response
    {
        $response = $this->sendAuthorized($this->accessToken(), $method, $path, $options);

        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey());
            $response = $this->sendAuthorized($this->accessToken(), $method, $path, $options);
        }

        return $response;
    }

    private function sendAuthorized(string $token, string $method, string $path, array $options = []): Response
    {
        try {
            return Http::acceptJson()
                ->withToken($token)
                ->timeout(10)
                ->send($method, self::API.$path, $options);
        } catch (ConnectionException $exception) {
            throw new ZoomException('network_error', $exception);
        }
    }

    private function accessToken(): string
    {
        $accountId = config('services.zoom.account_id');
        $clientId = config('services.zoom.client_id');
        $clientSecret = config('services.zoom.client_secret');

        if (! $accountId || ! $clientId || ! $clientSecret) {
            throw new ZoomException('credentials_missing');
        }

        return Cache::remember($this->tokenCacheKey(), now()->addMinutes(50), function () use ($accountId, $clientId, $clientSecret): string {
            try {
                $response = Http::asForm()
                    ->acceptJson()
                    ->withBasicAuth($clientId, $clientSecret)
                    ->timeout(10)
                    ->post('https://zoom.us/oauth/token?grant_type=account_credentials&account_id='.rawurlencode($accountId));
            } catch (ConnectionException $exception) {
                throw new ZoomException('network_error', $exception);
            }

            $token = $response->json('access_token');

            if (! $response->successful() || ! is_string($token) || $token === '') {
                throw new ZoomException('oauth_error_'.$response->status());
            }

            return $token;
        });
    }

    private function tokenCacheKey(): string
    {
        return 'zoom_access_token_'.hash(
            'sha256',
            (string) config('services.zoom.account_id').(string) config('services.zoom.client_id'),
        );
    }

    private function apiException(Response $response): ZoomException
    {
        return new ZoomException('zoom_api_error_'.$response->status());
    }

    private function validateZoomUrl(mixed $url): void
    {
        $host = is_string($url) ? parse_url($url, PHP_URL_HOST) : null;

        if (! is_string($url)
            || parse_url($url, PHP_URL_SCHEME) !== 'https'
            || ! is_string($host)
            || ! preg_match('/(^|\.)zoom\.us$/i', $host)) {
            throw new ZoomException('invalid_zoom_response');
        }
    }
}
