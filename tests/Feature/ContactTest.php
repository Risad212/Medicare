<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use App\Models\GeneralSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_loads(): void
    {
        $this->get(route('contact'))->assertOk();
    }

    public function test_contact_form_sends_email_to_configured_hospital_address(): void
    {
        Mail::fake();

        GeneralSetting::create([
            'site_name' => 'MediCare',
            'email' => 'care@medicare.test',
        ]);

        $this->from(route('contact'))->post(route('contact.submit'), [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '01711-123456',
            'subject' => 'Appointment question',
            'message' => 'Do you accept walk-ins?',
        ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Message sent successfully.');

        Mail::assertSent(ContactFormMail::class, function ($mail) {
            return $mail->hasTo('care@medicare.test');
        });
    }

    public function test_contact_form_falls_back_to_mail_from_address(): void
    {
        Mail::fake();

        $this->from(route('contact'))->post(route('contact.submit'), [
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'phone' => '01711-123456',
            'subject' => 'Appointment question',
            'message' => 'Do you accept walk-ins?',
        ])->assertRedirect();

        Mail::assertSent(ContactFormMail::class, function ($mail) {
            return $mail->hasTo(config('mail.from.address'));
        });
    }

    public function test_contact_page_shows_configured_map_embed(): void
    {
        $setting = GeneralSetting::create([
            'site_name' => 'MediCare',
            'map_embed_url' => 'https://maps.example.com/embed?q=hospital',
        ]);

        // The shared $setting view var is bound at app boot, before this
        // test's DB writes, so re-share it to mirror a live request.
        View::share('setting', $setting);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('maps.example.com/embed', false);
    }

    public function test_contact_page_hides_map_when_not_configured(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('maps.example.com/embed', false);
    }

    public function test_contact_form_validates_required_fields(): void
    {
        $this->from(route('contact'))->post(route('contact.submit'), [])
            ->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);
    }

    public function test_contact_form_rejects_invalid_email(): void
    {
        $this->from(route('contact'))->post(route('contact.submit'), [
            'name' => 'Jane Smith',
            'email' => 'not-an-email',
            'phone' => '01711-123456',
            'subject' => 'Hi',
            'message' => 'Hello',
        ])->assertSessionHasErrors('email');
    }
}
