<?php

namespace Tests\Feature;

use App\Jobs\SendSms;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\PatientNotifier;
use App\Services\Sms\OtpService;
use App\Services\Sms\SmsDeliveryException;
use App\Services\Sms\SmsGateway;
use App\Services\Sms\SmsGatewayFactory;
use App\Services\Sms\SmsSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Tests\Support\FakeSmsGateway;
use Tests\TestCase;

class SmsTest extends TestCase
{
    use RefreshDatabase;

    private FakeSmsGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeSmsGateway;
        app()->instance(SmsGateway::class, $this->gateway);
    }

    private function patient(array $attributes = []): User
    {
        return User::factory()->create($attributes + [
            'role' => 'patient',
            'phone' => '+880 1711-111111',
            'sms_enabled' => true,
        ]);
    }

    private function doctor(): Doctor
    {
        $user = User::factory()->create(['role' => 'doctor']);

        return Doctor::create([
            'name' => 'Dr. SMS Test',
            'slug' => 'dr-sms-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);
    }

    private function slot(): TimeSlot
    {
        return TimeSlot::create(['time' => '10:00 AM', 'status' => 1]);
    }

    private function appointment(?User $patient, array $attributes = []): Appointment
    {
        return Appointment::create($attributes + [
            'doctor_id' => $this->doctor()->id,
            'time_slot_id' => $this->slot()->id,
            'user_id' => $patient?->id,
            'patient_name' => $patient?->name ?? 'Guest Rider',
            'email' => 'sms-'.uniqid().'@example.test',
            'phone' => '+880 1811-111111',
            'gender' => 1,
            'visit_type' => 1,
            'consultation_mode' => 'in_person',
            'appointment_date' => now()->addDay()->toDateString(),
            'status' => 1,
        ]);
    }

    public function test_status_change_sms_reaches_opted_in_patient_with_normalized_number(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient);

        PatientNotifier::appointmentStatusChanged($appointment, 1);

        $this->assertCount(1, $this->gateway->messages);
        $this->assertSame('+8801711111111', $this->gateway->messages[0]['to']);
        $this->assertStringContainsString('approved', $this->gateway->messages[0]['message']);

        $log = ActivityLog::where('action', 'sms.sent')->first();
        $this->assertNotNull($log);
        $this->assertSame('+8801*********', $log->payload['to']);
        $this->assertSame('status_changed', $log->payload['type']);
    }

    public function test_status_change_sms_skips_patient_who_opted_out(): void
    {
        $patient = $this->patient(['sms_enabled' => false]);
        $appointment = $this->appointment($patient);

        PatientNotifier::appointmentStatusChanged($appointment, 1);

        $this->assertCount(0, $this->gateway->messages);
        $this->assertSame(1, $patient->notifications()->count());
    }

    public function test_booking_sms_reaches_guest_on_appointment_phone(): void
    {
        $appointment = $this->appointment(null);

        PatientNotifier::appointmentBooked($appointment);

        $this->assertCount(1, $this->gateway->messages);
        $this->assertSame('+8801811111111', $this->gateway->messages[0]['to']);
        $this->assertStringContainsString('Dr. SMS Test', $this->gateway->messages[0]['message']);
    }

    public function test_booking_sms_skips_linked_patient_without_phone(): void
    {
        $patient = $this->patient(['phone' => null]);
        $appointment = $this->appointment($patient);

        PatientNotifier::appointmentBooked($appointment);

        $this->assertCount(0, $this->gateway->messages);
    }

    public function test_reminder_command_sends_sms_and_stamps_reminder_sent_at(): void
    {
        $patient = $this->patient();
        $appointment = $this->appointment($patient, ['reminder_sent_at' => null]);

        $this->artisan('appointments:send-reminders')->assertExitCode(0);

        $messages = $this->gateway->messagesFor('+8801711111111');
        $this->assertCount(1, $messages);
        $this->assertStringContainsString('Reminder', $messages[0]['message']);
        $this->assertNotNull($appointment->fresh()->reminder_sent_at);
    }

    public function test_sms_module_disabled_blocks_all_outgoing_sms(): void
    {
        config(['modules.sms' => false]);
        $patient = $this->patient();
        $appointment = $this->appointment($patient);

        PatientNotifier::appointmentStatusChanged($appointment, 1);
        $this->assertFalse(app(OtpService::class)->issue($patient));

        $this->assertCount(0, $this->gateway->messages);
        $this->assertSame(0, ActivityLog::where('action', 'sms.sent')->count());
    }

    public function test_invalid_phone_is_skipped_and_sender_rejects_it(): void
    {
        $patient = $this->patient(['phone' => 'not-a-number']);
        $appointment = $this->appointment($patient);

        PatientNotifier::appointmentStatusChanged($appointment, 1);

        $this->assertCount(0, $this->gateway->messages);

        $sender = app(SmsSender::class);
        $this->assertNull($sender->normalize('call me later'));
        $this->assertSame('+8801711111111', $sender->normalize('+880 1711-111111'));
        $this->assertFalse($sender->send('12', 'hi', 'test'));
    }

    public function test_otp_roundtrip_wrong_code_and_lockout(): void
    {
        $patient = $this->patient();
        $otp = app(OtpService::class);

        $this->assertTrue($otp->issue($patient, 'login'));

        $message = $this->gateway->lastMessage()['message'];
        $this->assertSame(1, preg_match('/\b(\d{6})\b/', $message, $matches));
        $code = $matches[1];

        $this->assertTrue($otp->verify($patient, 'login', $code));
        $this->assertFalse($otp->verify($patient, 'login', $code));

        $this->assertTrue($otp->issue($patient, 'login'));
        $message = $this->gateway->lastMessage()['message'];
        preg_match('/\b(\d{6})\b/', $message, $matches);
        $code = $matches[1];

        for ($i = 0; $i < OtpService::MAX_ATTEMPTS; $i++) {
            $this->assertFalse($otp->verify($patient, 'login', '000000'));
        }

        $this->assertFalse($otp->verify($patient, 'login', $code));
    }

    public function test_otp_expires_after_five_minutes(): void
    {
        $patient = $this->patient();
        $otp = app(OtpService::class);

        $this->assertTrue($otp->issue($patient, 'login'));
        preg_match('/\b(\d{6})\b/', $this->gateway->lastMessage()['message'], $matches);

        $this->travel(OtpService::TTL_MINUTES + 1)->minutes();

        $this->assertFalse($otp->verify($patient, 'login', $matches[1]));
    }

    public function test_otp_is_scoped_to_purpose(): void
    {
        $patient = $this->patient();
        $otp = app(OtpService::class);

        $this->assertTrue($otp->issue($patient, 'login'));
        preg_match('/\b(\d{6})\b/', $this->gateway->lastMessage()['message'], $matches);

        $this->assertFalse($otp->verify($patient, 'reset', $matches[1]));
    }

    public function test_twilio_driver_posts_message_over_http(): void
    {
        config([
            'sms.driver' => 'twilio',
            'sms.providers.twilio.account_sid' => 'AC123',
            'sms.providers.twilio.auth_token' => 'secret',
            'sms.providers.twilio.from' => '+15550001111',
        ]);
        Http::fake();

        SmsGatewayFactory::make('twilio', (array) config('sms'))
            ->send('+8801711111111', 'hello there');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/Accounts/AC123/Messages.json')
                && $request['To'] === '+8801711111111'
                && $request['Body'] === 'hello there';
        });
    }

    public function test_twilio_driver_throws_on_rejection(): void
    {
        config([
            'sms.providers.twilio.account_sid' => 'AC123',
            'sms.providers.twilio.auth_token' => 'secret',
            'sms.providers.twilio.from' => '+15550001111',
        ]);
        Http::fake(['api.twilio.com/*' => Http::response([], 400)]);

        $this->expectException(SmsDeliveryException::class);

        SmsGatewayFactory::make('twilio', (array) config('sms'))->send('+8801711111111', 'hello');
    }

    public function test_unsupported_driver_fails_loudly(): void
    {
        $this->expectException(InvalidArgumentException::class);

        SmsGatewayFactory::make('carrier-pigeon', []);
    }

    public function test_failed_gateway_delivery_is_recorded_and_returns_false(): void
    {
        $failing = new class implements SmsGateway
        {
            public function send(string $to, string $message): void
            {
                throw new SmsDeliveryException('gateway down');
            }
        };
        app()->instance(SmsGateway::class, $failing);

        $this->assertFalse(app(SmsSender::class)->send('+8801711111111', 'hi', 'test'));
        $this->assertNotNull(ActivityLog::where('action', 'sms.failed')->first());
    }

    public function test_queued_job_delivers_through_sender(): void
    {
        SendSms::dispatchSync('+8801711111111', 'queued body', 'test');

        $this->assertSame([['to' => '+8801711111111', 'message' => 'queued body']], $this->gateway->messages);
    }

    public function test_profile_toggle_persists_sms_preference_both_ways(): void
    {
        $patient = $this->patient(['sms_enabled' => false]);

        $this->actingAs($patient)
            ->put(route('profile.update'), [
                'name' => $patient->name,
                'email' => $patient->email,
                'sms_enabled' => '1',
            ])
            ->assertRedirect();

        $this->assertTrue($patient->fresh()->sms_enabled);

        $this->actingAs($patient)
            ->put(route('profile.update'), [
                'name' => $patient->name,
                'email' => $patient->email,
                'sms_enabled' => '0',
            ])
            ->assertRedirect();

        $this->assertFalse($patient->fresh()->sms_enabled);
    }
}
