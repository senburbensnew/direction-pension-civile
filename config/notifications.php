<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS (Twilio)
    |--------------------------------------------------------------------------
    |
    | When enabled, notifications that define toTwilio() may also be sent by SMS
    | for notifiables that have a phone number (or an explicit twilio route).
    |
    */
    'sms_enabled' => env('NOTIFICATIONS_SMS_ENABLED', false),

    'sms_channel' => App\Notifications\Channels\TwilioSmsChannel::class,

];
