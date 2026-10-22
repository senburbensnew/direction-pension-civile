<?php

use App\Notifications\Channels\TwilioSmsChannel;

return [

    /*
    |--------------------------------------------------------------------------
    | SMS (Twilio / Vonage)
    |--------------------------------------------------------------------------
    |
    | When enabled, notifications that define toTwilio() may also be sent by SMS
    | for notifiables that have a phone number (or an explicit twilio route).
    |
    | sms_driver: twilio | vonage
    |
    */
    'sms_enabled' => env('NOTIFICATIONS_SMS_ENABLED', false),

    'sms_driver' => env('SMS_DRIVER', 'twilio'),

    'sms_channel' => TwilioSmsChannel::class,

];
