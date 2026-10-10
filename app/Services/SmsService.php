<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    public function send(string $to, string $message): void
    {
        $to = preg_replace('/\s+/', '', trim($to)) ?? '';

        if ($to === '' || $message === '') {
            return;
        }

        $sid = config('services.twilio.account_sid');
        $token = config('services.twilio.auth_token');
        $from = config('services.twilio.from');

        if (! filled($sid) || ! filled($token) || ! filled($from)) {
            return;
        }

        $client = new Client($sid, $token);

        $client->messages->create(
            $to,
            [
                'from' => $from,
                'body' => $message,
            ]
        );
    }
}
