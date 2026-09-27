<?php

/*
 * Static contact configuration.
 * Dynamic data (directions, services, contact info) is stored in the database.
 * See: DirectionDepartementaleSeeder, ServiceSeeder, ParametersSeeder.
 */

return [
    /*
     * Lignes téléphoniques des services, telles que communiquées par la DPC.
     * phone : format E.164 pour les liens tel:
     * display : présentation publique
     */
    'service_phones' => [
        [
            'label' => 'Pour tout suivi de chèque de pension et de virement',
            'phone' => '+50947912007',
            'display' => '+(509) 47 91 2007',
        ],
        [
            'label' => 'Pour les avals',
            'phone' => '+50929405829',
            'display' => '+(509) 29 40 5829',
        ],
        [
            'label' => 'Pour toute demande de pension',
            'phone' => '+50929407129',
            'display' => '+(509) 29 40 7129',
        ],
        [
            'label' => 'Pour les demandes d’attestation de pension',
            'phone' => '+50929405829',
            'display' => '+(509) 29 40 5829',
        ],
        [
            'label' => 'Pour le suivi des dossiers d’assurance et de mandat',
            'phone' => '+50947912006',
            'display' => '+(509) 47 91 2006',
        ],
    ],
];
