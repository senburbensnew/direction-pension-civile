<?php

return [

    'quality' => (int) env('MEDIA_IMAGE_QUALITY', 82),

    'responsive_widths' => [480, 960, 1600],

    'upload_max_widths' => [
        'carousel' => 1920,
        'actualite' => 1600,
        'official' => 1200,
        'partenaire' => 800,
        'institution' => 1600,
        'mediatheque' => 1920,
        'profile' => 512,
    ],

];
