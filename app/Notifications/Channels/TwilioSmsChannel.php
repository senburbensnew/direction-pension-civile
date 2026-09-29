<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class TwilioSmsChannel
{
    /**
     * Envoie la notification via Twilio SMS.
     */
    public function send(object $notifiable, Notification $notification): void
    {
        if (! method_exists($notification, 'toTwilio')) {
            return;
        }

        $message = $notification->toTwilio($notifiable);

        if (empty($message['to']) || empty($message['message'])) {
            return;
        }

        $client = new Client(
            config('services.twilio.account_sid'),
            config('services.twilio.auth_token')
        );

        $client->messages->create(
            $message['to'],
            [
                'from' => config('services.twilio.from'),
                'body' => $message['message'],
            ]
        );
    }
}