<?php

namespace App\Mail;

use App\Models\LabOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LabResultReadyMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(public LabOrder $labOrder) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your lab results are ready - MediCare',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.lab-result-ready',
            with: [
                'labOrder' => $this->labOrder,
            ],
        );
    }
}
