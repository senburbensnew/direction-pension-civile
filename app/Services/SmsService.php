<?php

namespace App\Services;

use Twilio\Rest\Client;

class SmsService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /* 
        Exemple d'utilisation :
        use App\Services\SmsService;

        $sms = app(SmsService::class);
        $sms->send('+509xxxxxxxx', 'Votre demande a été enregistrée avec succès.'); 
    */

    public function send(
        string $to,
        string $message
    ): void {
        $this->client->messages->create(
            $to,
            [
                'from' => config('services.twilio.from'),
                'body' => $message,
            ]
        );
    }
}