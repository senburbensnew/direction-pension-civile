<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site identity (favicon, search engines, social previews)
    |--------------------------------------------------------------------------
    */

    'organization_name' => env('SEO_ORGANIZATION_NAME', 'Direction de la Pension Civile'),

    'description' => env(
        'SEO_DESCRIPTION',
        'Portail officiel de la Direction de la Pension Civile — services aux pensionnés, fonctionnaires et institutions, actualités et démarches en ligne.'
    ),

    /** Public path under /public used for favicons (generated from the site logo). */
    'favicon_dir' => 'images/brand',

    /** Square PNG for Open Graph / Twitter (min. 200×200; 512 recommended). */
    'og_image' => 'images/brand/og-image.png',

    /** Square PNG for schema.org Organization logo (min. 112×112). */
    'logo' => 'images/brand/logo-512.png',

    'twitter_site' => env('SEO_TWITTER_SITE'),

];
