<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_queues_mail_and_redirects_success(): void
    {
        Mail::fake();

        $this->from('/contact')
            ->post(route('contact.submit'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.test',
                'phone' => '+1 555 0100',
                'subject' => 'General enquiry',
                'message' => 'Hello, I have a question.',
            ])
            ->assertRedirect('/contact')
            ->assertSessionHas('success');

        Mail::assertQueued(ContactFormMail::class);
    }

    public function test_contact_form_validates_field_lengths(): void
    {
        Mail::fake();

        $this->from('/contact')
            ->post(route('contact.submit'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.test',
                'phone' => str_repeat('1', 21),
                'subject' => 'Hi',
                'message' => 'short',
            ])
            ->assertSessionHasErrors('phone');

        $this->from('/contact')
            ->post(route('contact.submit'), [
                'name' => 'Jane Doe',
                'email' => 'jane@example.test',
                'phone' => '555-0100',
                'subject' => 'Hi',
                'message' => str_repeat('x', 5001),
            ])
            ->assertSessionHasErrors('message');

        Mail::assertNothingQueued();
    }
}
