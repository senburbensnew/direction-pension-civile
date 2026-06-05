<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\NewsletterCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public NewsletterCampaign $campaign,
        public Newsletter $subscriber,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->campaign->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.newsletter');
    }
}
