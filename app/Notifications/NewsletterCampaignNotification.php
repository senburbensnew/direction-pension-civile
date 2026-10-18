<?php

namespace App\Notifications;

use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
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
        public readonly ?NewsletterDelivery $delivery = null,
    ) {}

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable, database: false);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $fromAddress = config('mail.from.address');
        $fromName = $this->campaign->from_name ?: config('newsletter.from_name');

        $message = (new MailMessage)
            ->from($fromAddress, $fromName)
            ->subject($this->campaign->subject)
            ->view('emails.newsletter', [
                'campaign' => $this->campaign,
                'subscriber' => $this->subscriber,
                'delivery' => $this->delivery,
            ]);

        if ($this->campaign->preview_text) {
            $preview = $this->campaign->preview_text;
            $message->withSymfonyMessage(function ($email) use ($preview) {
                $email->getHeaders()->addTextHeader('X-PM-Metadata-preview', $preview);
            });
        }

        if ($this->campaign->reply_to) {
            $message->replyTo($this->campaign->reply_to);
        }

        return $message;
    }
}
