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

    private function extractInput(string $html, string $name): string
    {
        if (preg_match('/<(input|select)[^>]*name=["\']'.preg_quote($name, '/').'["\'][^>]*>/is', $html, $m)) {
            return $m[0];
        }

        return '';
    }
}
