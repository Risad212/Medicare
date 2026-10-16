<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmedMail;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Prescription;
use App\Models\TimeSlot;
use App\Models\User;
use App\Notifications\AppointmentStatusChangedPatient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationCoverageTest extends TestCase
{
    use RefreshDatabase;

    private const LOCALES = ['ar', 'bn', 'de', 'en', 'es', 'fr', 'it', 'pt', 'ru', 'tr', 'zh'];

    private const SECTIONS = ['notifications', 'mail', 'pdf', 'public', 'mail_body', 'pdf_body', 'staff'];

    public function test_validation_files_exist_and_are_translated_for_all_locales(): void
    {
        $english = require base_path('lang/en/validation.php');

        foreach (self::LOCALES as $locale) {
            $path = base_path("lang/{$locale}/validation.php");
            $this->assertFileExists($path, "Missing validation.php for [{$locale}]");
            $messages = require $path;
            $this->assertSame(
                array_keys($english),
                array_keys($messages),
                "validation.php key mismatch for [{$locale}]"
            );
            if ($locale !== 'en') {
                $this->assertNotSame(
                    $english['required'],
                    $messages['required'],
                    "validation.required for [{$locale}] looks untranslated"
                );
            }
        }
    }

    public function test_message_sections_have_key_parity_across_all_locales(): void
    {
        $english = require base_path('lang/en/messages.php');

        foreach (self::LOCALES as $locale) {
            $messages = require base_path("lang/{$locale}/messages.php");
            foreach (self::SECTIONS as $section) {
                $this->assertArrayHasKey($section, $messages, "Missing [{$section}] for [{$locale}]");
                $this->assertSame(
                    array_keys($english[$section]),
                    array_keys($messages[$section]),
                    "[{$section}] key mismatch for [{$locale}]"
                );
            }
            $this->assertSame(
                array_keys($english['prescription']['validation']),
                array_keys($messages['prescription']['validation']),
                "[prescription.validation] key mismatch for [{$locale}]"
            );
        }
    }

    public function test_appointment_validation_errors_use_the_request_locale(): void
    {
        $this->withSession(['locale' => 'es'])
            ->withHeaders(['Referer' => route('appointment')])
            ->followingRedirects()
            ->post(route('appointment.store'), [])
            ->assertOk()
            ->assertSee('Este campo es obligatorio.', false);
    }

    public function test_notification_payload_uses_the_recipient_locale(): void
    {
        [, $doctor] = $this->makeDoctor();
        $patient = User::factory()->create(['role' => 'patient', 'locale' => 'bn']);
        $appointment = $this->makeAppointment($doctor, 1, $patient->id);

        $payload = (new AppointmentStatusChangedPatient($appointment, 1))->toDatabase($patient);

        $this->assertSame('অ্যাপয়েন্টমেন্ট অনুমোদিত', $payload['title']);
        $this->assertStringNotContainsString('Your appointment', $payload['message']);
    }

    public function test_mail_subject_uses_the_send_locale(): void
    {
        [, $doctor] = $this->makeDoctor();
        $appointment = $this->makeAppointment($doctor, 1);

        app()->setLocale('bn');
        $subject = (new AppointmentConfirmedMail($appointment))->envelope()->subject;

        $this->assertSame('আপনার অ্যাপয়েন্টমেন্ট নিশ্চিত করা হয়েছে - মেডিকেয়ার', $subject);
    }

    public function test_prescription_pdf_renders_localized_chrome_with_rtl_in_arabic(): void
    {
        [, $doctor] = $this->makeDoctor();
        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_name' => 'Test Patient',
            'diagnosis' => 'Flu',
        ]);

        app()->setLocale('ar');
        $html = view('pdf.prescription', [
            'prescription' => $prescription->loadMissing('items', 'doctor', 'appointment'),
            'setting' => null,
        ])->render();

        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $html);
        $this->assertStringContainsString('وصفة طبية', $html);
        $this->assertStringContainsString('Flu', $html);
        $this->assertStringNotContainsString('<h2>Prescription</h2>', $html);
    }

    public function test_admin_sidebar_renders_in_arabic(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession(['locale' => 'ar'])
            ->get(route('admin.languages.index'))
            ->assertOk()
            ->assertSee('لوحة التحكم', false)
            ->assertSee('تسجيل الخروج', false);
    }

    public function test_cancel_page_renders_in_spanish(): void
    {
        [, $doctor] = $this->makeDoctor();
        $appointment = $this->makeAppointment($doctor, 0);
        $appointment->forceFill(['cancellation_token' => 'test-token-123'])->save();

        $this->withSession(['locale' => 'es'])
            ->get(route('appointment.cancel-page', 'test-token-123'))
            ->assertOk()
            ->assertSee('Detalles de la cita', false)
            ->assertSee('Cancelar mi cita', false);
    }

    private function makeDoctor(): array
    {
        $user = User::factory()->create(['role' => 'doctor']);
        $doctor = Doctor::create([
            'name' => 'Locale Doctor',
            'slug' => 'locale-doctor-'.uniqid(),
            'user_id' => $user->id,
            'status' => 1,
        ]);

        return [$user, $doctor];
    }

    private function makeAppointment(Doctor $doctor, int $status, ?int $userId = null): Appointment
    {
        $slot = TimeSlot::create(['time' => '10:00 AM', 'status' => 1]);

        return Appointment::create([
            'doctor_id' => $doctor->id,
            'time_slot_id' => $slot->id,
            'user_id' => $userId,
            'patient_name' => 'Locale Patient',
            'email' => 'locale-patient@example.test',
            'phone' => '555-0100',
            'gender' => 1,
            'visit_type' => 1,
            'appointment_date' => now()->addDays(2)->toDateString(),
            'status' => $status,
        ]);
    }
}
