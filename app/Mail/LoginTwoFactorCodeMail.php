<?php

namespace App\Mail;

use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LoginTwoFactorCodeMail extends Mailable implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable, SerializesModels;

    public function __construct(public string $code)
    {
        $this->configureQueueRetries();
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.login_two_factor_email_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.login-two-factor-code',
            with: [
                'code' => $this->code,
            ],
        );
    }
}
