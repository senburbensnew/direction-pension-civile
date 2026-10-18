<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IP geolocation (connexions)
    |--------------------------------------------------------------------------
    |
    | Résolution pays / région / ville via ip-api.com (HTTP, usage non commercial).
    | Désactiver si l'hébergement bloque les requêtes sortantes.
    |
    */

    'enabled' => env('GEOLOCATION_ENABLED', true),

    'provider_url' => env('GEOLOCATION_PROVIDER_URL', 'http://ip-api.com/json/{ip}'),

    'timeout_seconds' => (int) env('GEOLOCATION_TIMEOUT', 3),

    'cache_ttl_seconds' => (int) env('GEOLOCATION_CACHE_TTL', 604800),

];
