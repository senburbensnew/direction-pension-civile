<?php

use App\Models\Service;

/*
|--------------------------------------------------------------------------
| Motifs de rendez-vous (évolutif)
|--------------------------------------------------------------------------
|
| Chaque motif définit :
|   - label : libellé affiché
|   - service : code du service responsable (Service::*)
|   - documents : pièces à joindre (clés = collections Spatie Media)
|
| Les clés de documents sont partagées entre motifs lorsque le sens est identique.
|
*/

return [

    'motifs' => [

        'demande_formalites_mandats' => [
            'label' => 'Demande de formalités de pension et mandats',
            'service' => Service::FORMALITE,
            'documents' => [
                'single' => [
                    'carte_pension' => [
                        'label' => 'Carte de pension (photo ou scan)',
                        'required' => true,
                        'mimes' => 'pdf,jpg,jpeg,png',
                        'max_kb' => 5120,
                    ],
                    'piece_identite' => [
                        'label' => 'Pièce d’identité (CIN, NIF ou passeport)',
                        'required' => true,
                        'mimes' => 'pdf,jpg,jpeg,png',
                        'max_kb' => 5120,
                    ],
                    'matricule_fiscal' => [
                        'label' => 'Copie du matricule fiscal',
                        'required' => true,
                        'mimes' => 'pdf,jpg,jpeg,png',
                        'max_kb' => 5120,
                    ],
                ],
                'multiple' => [
                    'pieces_mandat' => [
                        'label' => 'Documents relatifs au mandat (si applicable)',
                        'required' => false,
                        'min_files' => 0,
                        'max_files' => 5,
                        'mimes' => 'pdf,jpg,jpeg,png,doc,docx',
                        'max_kb' => 5120,
                    ],
                ],
            ],
            'prepare' => [
                'physique' => [
                    'Carte de pension (originale ou copie certifiée) — si vous en disposez',
                    'Pièce d’identité valide',
                    'Matricule fiscal — ouverture de compte en ligne en attente de validation',
                    'Pièces déjà téléversées lors de la demande de compte en ligne',
                    'Tout document relatif au mandat ou à la formalité demandée',
                ],
                'visio' => [
                    'Carte de pension à présenter à l’écran — si applicable',
                    'Pièce d’identité à présenter à l’écran',
                    'Matricule fiscal à l’écran — ouverture de compte en attente',
                    'Documents numériques relatifs au mandat (si applicable)',
                ],
            ],
        ],

        // Motifs prévus — activer en décommentant ou en complétant la configuration.
        /*
        'demande_pension' => [
            'label' => 'Demande de pension / liquidation',
            'service' => Service::LIQUIDATION,
            'documents' => [...],
        ],
        */
    ],

    /*
    | Décision sur une demande de création de compte (compte provisoire) :
    | déclenchée après un RDV réalisé pour un motif du service Formalités —
    | pas de motif RDV dédié « ouverture de compte ».
    */
    'account_decision' => [
        'service' => Service::FORMALITE,
        'legacy_motif_keys' => [
            'finalisation_creation_compte',
        ],
    ],

    /*
    | Motifs réservables en ligne (portail pension + compte provisoire).
    | - motif_keys : liste explicite (prioritaire si non vide)
    | - services : sinon tous les motifs rattachés à ces services
    */
    'portal_bookable' => [
        'motif_keys' => [],
        'services' => [
            Service::FORMALITE,
        ],
    ],

    /*
    | Documents complémentaires libres (tous motifs).
    */
    'supplement' => [
        'label' => 'Document complémentaire',
        'max_files' => 10,
        'mimes' => 'pdf,jpg,jpeg,png,doc,docx',
        'max_kb' => 5120,
        'collection' => 'rdv_supplement',
    ],

    /*
    | Rôles autorisés à réattribuer un rendez-vous (agent et/ou créneau).
    */
    'reassign_roles' => [
        'admin',
        'directeur',
        'assistant_directeur',
        'validateur_rdv',
        'responsable_service_accueil_formalites',
    ],

    /*
    | Pilotage RDV : consulter et réattribuer l’agent (sans valider, clôturer, etc.).
    */
    'pilotage_reassign_only_roles' => [
        'responsable_service_accueil_formalites',
    ],

];
