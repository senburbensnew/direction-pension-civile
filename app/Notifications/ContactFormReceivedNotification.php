<?php

namespace App\Notifications;

use App\Models\Contact;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContactFormReceivedNotification extends Notification implements ShouldQueue
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
            ->replyTo([$this->contact->email])
            ->subject('[Contact] '.$this->contact->subjectLabel())
            ->view('emails.contact', ['contact' => $this->contact]);
    }
}
