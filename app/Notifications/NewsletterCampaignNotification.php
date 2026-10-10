<?php

namespace App\Notifications;

use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Notifications\Concerns\UsesStandardChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewsletterCampaignNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use UsesStandardChannels;

    public function __construct(
        public readonly NewsletterCampaign $campaign,
        public readonly Newsletter $subscriber,
    ) {
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable, database: false);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->campaign->subject)
            ->view('emails.newsletter', [
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
            ]);
    }
}
