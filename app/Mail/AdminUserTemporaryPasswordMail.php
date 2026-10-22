<?php

namespace App\Mail;

use App\Models\User;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminUserTemporaryPasswordMail extends Mailable implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $temporaryPassword,
        public User $initiatedBy,
    ) {
        $this->configureQueueRetries();
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réinitialisation de votre mot de passe — Direction de la Pension Civile',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.admin-user-temporary-password',
            with: [
                'displayName' => $this->user->displayName(),
                'email' => $this->user->email,
                'phone' => $this->user->routeNotificationForTwilio(),
                'temporaryPassword' => $this->temporaryPassword,
                'loginUrl' => route('login'),
            ],
        );
    }
}
