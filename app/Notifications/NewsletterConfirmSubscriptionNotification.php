<?php

namespace App\Notifications;

use App\Models\Newsletter;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewsletterConfirmSubscriptionNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(public readonly Newsletter $subscriber)
    {
        $this->configureQueueRetries();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable, database: false);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('newsletter.confirm', ['token' => $this->subscriber->confirmation_token]);

        return (new MailMessage)
            ->subject(__('newsletter.confirm_email_subject'))
            ->markdown('emails.newsletter-confirm', [
                'subscriber' => $this->subscriber,
                'confirmUrl' => $url,
            ]);
    }
}
