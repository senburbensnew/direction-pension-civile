<?php

return [

    /*
    | Navigation portail : bénéficiaires / mandats visibles pour les pensionnés.
    | Désactivé par défaut (« pour le moment »).
    */
    'show_pensionne_beneficiaires_mandats_nav' => env(
        'PENSIONNE_SHOW_BENEFICIAIRES_MANDATS_NAV',
        false
    ),

];
