<?php

namespace App\Notifications;

use App\Models\Contact;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormAcknowledgmentNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(public readonly Contact $contact)
    {
        $this->configureQueueRetries();
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable, database: false);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Accusé de réception — Direction de la Pension Civile')
            ->view('emails.contact-acknowledgment', ['contact' => $this->contact]);
    }
}
