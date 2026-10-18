<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Double opt-in
    |--------------------------------------------------------------------------
    */
    'double_opt_in' => env('NEWSLETTER_DOUBLE_OPT_IN', true),

    'confirmation_ttl_hours' => (int) env('NEWSLETTER_CONFIRMATION_TTL_HOURS', 48),

    /*
    |--------------------------------------------------------------------------
    | Public form rate limiting (attempts per minute per IP)
    |--------------------------------------------------------------------------
    */
    'subscribe_rate_limit' => (int) env('NEWSLETTER_SUBSCRIBE_RATE_LIMIT', 5),

    /*
    |--------------------------------------------------------------------------
    | Dispatch
    |--------------------------------------------------------------------------
    */
    'batch_size' => (int) env('NEWSLETTER_BATCH_SIZE', 50),

    'batch_delay_seconds' => (int) env('NEWSLETTER_BATCH_DELAY_SECONDS', 2),

    'max_send_retries' => (int) env('NEWSLETTER_MAX_SEND_RETRIES', 3),

    /*
    |--------------------------------------------------------------------------
    | Defaults for outbound campaigns
    |--------------------------------------------------------------------------
    */
    'from_name' => env('NEWSLETTER_FROM_NAME', env('MAIL_FROM_NAME', 'Direction de la Pension Civile')),

    'reply_to' => env('NEWSLETTER_REPLY_TO', env('MAIL_FROM_ADDRESS')),

    /*
    |--------------------------------------------------------------------------
    | Tracking (privacy-sensitive — disabled by default)
    |--------------------------------------------------------------------------
    */
    'open_tracking' => env('NEWSLETTER_OPEN_TRACKING', false),

    'click_tracking' => env('NEWSLETTER_CLICK_TRACKING', false),

    /*
    |--------------------------------------------------------------------------
    | Webhooks (bounce / delivery events from ESP)
    |--------------------------------------------------------------------------
    */
    'webhook_secret' => env('NEWSLETTER_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Data retention (days) for delivery logs and audit entries
    |--------------------------------------------------------------------------
    */
    'retention_days' => (int) env('NEWSLETTER_RETENTION_DAYS', 730),

    /*
    |--------------------------------------------------------------------------
    | Audience groups (public / segmentation)
    |--------------------------------------------------------------------------
    */
    'audience_groups' => [
        'externe' => 'Public général',
        'pensionne' => 'Pensionnés',
        'fonctionnaire' => 'Fonctionnaires',
        'institution' => 'Institutions',
    ],

    'preference_topics' => [
        'actualites' => 'Actualités',
        'annonces' => 'Annonces officielles',
        'prestations' => 'Prestations et services',
    ],

    'locales' => ['fr', 'en', 'ht'],

];
