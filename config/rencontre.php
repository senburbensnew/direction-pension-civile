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

        'finalisation_creation_compte' => [
            'label' => 'Finalisation de la création de compte',
            'service' => Service::FORMALITE,
            'documents' => [
                'single' => [
                    'piece_identite' => [
                        'label' => 'Pièce d’identité (CIN, passeport ou permis)',
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
                'multiple' => [],
            ],
            'prepare' => [
                'physique' => [
                    'Pièce d’identité valide (originale ou copie certifiée)',
                    'Matricule fiscal (document ou original selon les instructions de l’agent)',
                    'Acte de naissance — si le pensionné est mineur',
                    'Pièce d’identité du représentant légal — si le pensionné est mineur',
                    'Impression ou fichier des pièces déjà téléversées lors de la demande de compte en ligne',
                ],
                'visio' => [
                    'Pièce d’identité à présenter clairement à l’écran',
                    'Matricule fiscal à présenter à l’écran',
                    'Acte de naissance du mineur et pièce du représentant — le cas échéant',
                    'Fichiers numériques des documents déjà joints à votre demande de compte',
                ],
            ],
        ],

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
                    'Carte de pension (originale ou copie certifiée)',
                    'Pièce d’identité valide',
                    'Tout document relatif au mandat ou à la formalité demandée',
                ],
                'visio' => [
                    'Carte de pension à présenter à l’écran',
                    'Pièce d’identité à présenter à l’écran',
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
        'direction',
        'validateur_rdv',
        'responsable_service_accueil_formalites',
    ],

];
