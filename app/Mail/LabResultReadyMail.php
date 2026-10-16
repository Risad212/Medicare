<?php

namespace App\Mail;

use App\Modules\Lab\Models\LabOrder;
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
            subject: __('messages.mail.lab_subject'),
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
