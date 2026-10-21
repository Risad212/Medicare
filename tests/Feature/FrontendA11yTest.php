<?php

namespace Tests\Feature;

use App\Models\Slider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendA11yTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Every public page must render exactly one H1. Breadcrumb partials
     * provide it; the homepage uses the first slider title.
     */
    public function test_public_pages_render_exactly_one_h1(): void
    {
        Slider::create([
            'title' => 'Care that revolves around you',
            'description' => 'Modern hospital care for the whole family',
            'button_text' => 'Book now',
            'bg_image' => '',
        ]);

        $guestRoutes = [
            'home',
            'about',
            'doctor',
            'service',
            'blog',
            'contact',
            'appointment',
        ];

        foreach ($guestRoutes as $route) {
            $response = $this->get(route($route));
            $response->assertOk();

            $html = (string) $response->getContent();
            $count = substr_count($html, '<h1');

            $this->assertSame(
                1,
                $count,
                "Expected exactly one <h1> on [{$route}], found {$count}."
            );
        }
    }

    /**
     * Appointment form: every input is label-bound and carries HTML
     * validation attributes mirroring AppointmentController::store rules.
     */
    public function test_appointment_form_labels_and_mirrored_constraints(): void
    {
        $response = $this->get(route('appointment'))->assertOk();
        $html = (string) $response->getContent();

        foreach (['patient_name', 'phone', 'doctor_id', 'visit_type', 'gender', 'appointment_date', 'time_slot_id'] as $field) {
            $this->assertStringContainsString(
                'for="'. $field .'"',
                $html,
                "Missing bound label for [{$field}]."
            );
            $this->assertStringContainsString(
                'id="'. $field .'"',
                $html,
                "Missing id on [{$field}]."
            );
        }

        // Server side these are "required" — mirror with required attributes.
        foreach (['patient_name', 'phone', 'visit_type', 'gender', 'appointment_date', 'time_slot_id'] as $field) {
            $input = $this->extractInput($html, $field);
            $this->assertMatchesRegularExpression(
                '/<(input|select)[^>]*'.preg_quote($field, '/').'[^>]*\brequired\b/is',
                $input,
                "Missing required attribute on [{$field}]."
            );
        }

        $this->assertStringContainsString('inputmode="tel"', $html);
        $this->assertStringContainsString('inputmode="numeric"', $html);
        $this->assertStringContainsString('autocomplete="name"', $html);
        $this->assertStringContainsString('autocomplete="tel"', $html);
        $this->assertStringContainsString('autocomplete="email"', $html);
        $this->assertStringContainsString('maxlength="20"', $html); // phone
        $this->assertStringContainsString('min="0"', $html);        // age
        $this->assertStringContainsString('max="120"', $html);      // age
        $this->assertStringNotContainsString('aria-label="Appointment date"', $html); // replaced by bound label
    }

    /**
     * Doctor detail embedded booking form mirrors AppointmentController
     * rules too: visible labels are bound with for/id, all server-required
     * fields carry required attributes, and no hardcoded English survives.
     */
    public function test_doctor_detail_booking_form_labels_and_mirrored_constraints(): void
    {
        $user = \App\Models\User::factory()->create(['role' => 'doctor']);
        $doctor = \App\Models\Doctor::create([
            'name' => 'Form Doctor',
            'slug' => 'form-doctor-'.uniqid(),
            'department' => 'Cardiology',
            'user_id' => $user->id,
            'status' => 1,
        ]);

        $response = $this->get(route('doctor.show', $doctor))->assertOk();
        $html = (string) $response->getContent();

        // Visible content only — script blocks (JS identifiers, JSON-LD)
        // are never user-visible.
        $visible = trim(preg_replace('/<script\b[^>]*>.*?<\/script>/is', ' ', $html) ?? $html);

        // No hardcoded booking-form English strings remain.
        foreach (['Make an Appointment', 'Visit Type', 'Patient Name', 'Age', 'Gender', 'Phone', 'Email', 'Appointment Date', 'Time Slot', 'Send Request', 'First Visit', 'Second Visit', 'Report Review', 'Select Gender'] as $raw) {
            $this->assertStringNotContainsString($raw, $visible, "Hardcoded string [{$raw}] still present.");
        }

        // Labels are bound with for/id pairs — patient_age uses a distinct
        // handle from the standalone page's shared ids.
        foreach (['doctor_name_display', 'visit_type', 'patient_name', 'patient_age', 'patient_gender', 'patient_phone', 'patient_email', 'appointment_date', 'time_slot'] as $id) {
            $this->assertStringContainsString('for="'.$id.'"', $html, "Label for [{$id}] not bound.");
            $this->assertStringContainsString('id="'.$id.'"', $html, "Missing id [{$id}].");
        }

        // Server-required fields carry required.
        foreach (['patient_name', 'patient_age', 'patient_gender', 'patient_phone', 'appointment_date'] as $id) {
            $this->assertMatchesRegularExpression(
                '/<(input|select)[^>]*id=["\']'.preg_quote($id, '/').'["\'][^>]*\brequired\b/is',
                $html,
                "Missing required attribute on [{$id}]."
            );
        }

        $this->assertMatchesRegularExpression('/<select[^>]*id=["\']visit_type["\'][^>]*required/is', $html);
        $this->assertMatchesRegularExpression('/<select[^>]*id=["\']time_slot["\'][^>]*required/is', $html);
        $this->assertMatchesRegularExpression(
            '/<input[^>]*id=["\']appointment_date["\'][^>]*\bmin="\d{4}-\d{2}-\d{2}"/is',
            $html,
            'Appointment date input lost its min floor.'
        );
        $this->assertStringContainsString('maxlength="255"', $html);
        $this->assertStringContainsString('autocomplete="name"', $html);
        $this->assertStringContainsString('autocomplete="tel"', $html);
        $this->assertStringContainsString('autocomplete="email"', $html);
        $this->assertMatchesRegularExpression('/id=["\']patient_age["\'][^>]*\bmin="0"/is', $html);
        $this->assertMatchesRegularExpression('/id=["\']patient_age["\'][^>]*\bmax="120"/is', $html);
        $this->assertMatchesRegularExpression('/<h1/is', $html); // page keeps its single h1 (asserted elsewhere)
    }

    private function extractInput(string $html, string $name): string
    {
        if (preg_match('/<(input|select)[^>]*name=["\']'.preg_quote($name, '/').'["\'][^>]*>/is', $html, $m)) {
            return $m[0];
        }

        return '';
    }
}
