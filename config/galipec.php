<?php

return [

    'driver' => env('GALIPEC_DRIVER', 'http'),

    'enabled' => env('GALIPEC_ENABLED', true),

    'base_url' => env('GALIPEC_BASE_URL'),

    'token' => env('GALIPEC_TOKEN'),

    'timeout' => (int) env('GALIPEC_TIMEOUT', 15),

    /*
     * Exiger la correspondance GALIPEC avant de déposer une demande de compte.
     * Désactivé pour le moment : l’étape est sautée.
     */
    'required' => (bool) env('GALIPEC_REQUIRED', false),

    /*
    | Courriel de notification — à l'étude, non envoyé pour le moment.
    */
    'notify_email' => env('GALIPEC_NOTIFY_EMAIL', false),

];
