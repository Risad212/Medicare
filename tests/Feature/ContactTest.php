<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_loads(): void
    {
        $this->get(route('contact'))->assertOk();
    }

    public function test_contact_form_sends_email_to_hospital(): void
    {
        Mail::fake();

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
            return $mail->hasTo('hospital@gmail.com');
        });
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
