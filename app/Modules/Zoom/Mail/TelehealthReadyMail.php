<?php

namespace App\Modules\Zoom\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TelehealthReadyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public string $joinUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your private telehealth link - MediCare');
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'zoom.telehealth-ready',
            with: [
                'appointment' => $this->appointment,
                'joinUrl' => $this->joinUrl,
            ],
        );
    }
}
