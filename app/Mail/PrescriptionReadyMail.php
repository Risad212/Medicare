<?php

namespace App\Mail;

use App\Models\Prescription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrescriptionReadyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public Prescription $prescription) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your prescription is ready - MediCare',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.prescription-ready',
            with: [
                'prescription' => $this->prescription,
            ],
        );
    }
}
