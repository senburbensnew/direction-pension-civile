<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Journaux de développement (APP_ENV=local uniquement)
    |--------------------------------------------------------------------------
    |
    | DEV_LOG_OTP → storage/logs/otp-dev.log
    |
    | E-mails en local : MAIL_MAILER=mailpit (Mailpit) ou mail → mail-dev.log
    |
    */

    'log_otp' => filter_var(env('DEV_LOG_OTP', false), FILTER_VALIDATE_BOOL),

    'log_mail' => filter_var(env('DEV_LOG_MAIL', false), FILTER_VALIDATE_BOOL),

];
