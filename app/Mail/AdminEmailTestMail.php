<?php

namespace App\Mail;

use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminEmailTestMail extends Mailable implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable, SerializesModels;

    public function __construct(
        public string $bodyText,
        public string $mailSubject,
    ) {
        $this->configureQueueRetries();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.admin-email-test',
            with: [
                'bodyText' => $this->bodyText,
            ],
        );
    }
}
