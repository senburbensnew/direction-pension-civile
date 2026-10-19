<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Catégories de compte (un type par compte ; segments portail)
    |--------------------------------------------------------------------------
    |
    | Source de vérité pour user_categories + UserCategoriesSeeder.
    | default_roles : rôles Spatie assignés lors de l'ajout de la catégorie
    | (via UserCategoryRoleSyncService), sans retirer les autres rôles métier.
    |
    */

    'categories' => [
        'pensionne' => [
            'label' => 'Pensionné',
            'description' => 'Titulaire adulte avec accès portail pensionné (demandes, corbeille).',
            'is_system' => true,
            'capabilities' => [
                'portal_home' => 'pensionne',
                'requires_beneficiaire' => false,
                'can_hold_service' => false,
            ],
            'default_roles' => ['pensionne'],
        ],
        'fonctionnaire' => [
            'label' => 'Fonctionnaire',
            'description' => 'Demandeur fonction publique (hors personnel DPC).',
            'is_system' => true,
            'capabilities' => [
                'portal_home' => 'fonctionnaire',
                'requires_beneficiaire' => false,
                'can_hold_service' => false,
            ],
            'default_roles' => ['fonctionnaire'],
        ],
        'institution' => [
            'label' => 'Institution',
            'description' => 'Compte unique rattaché à une institution.',
            'is_system' => true,
            'capabilities' => [
                'portal_home' => 'institution',
                'requires_beneficiaire' => false,
                'can_hold_service' => false,
                'requires_institution' => true,
            ],
            'default_roles' => ['institution'],
        ],
        'personnel_dpc' => [
            'label' => 'Personnel DPC',
            'description' => 'Agents, responsables de service et direction.',
            'is_system' => true,
            'capabilities' => [
                'portal_home' => 'agent',
                'requires_beneficiaire' => false,
                'can_hold_service' => true,
            ],
            'default_roles' => [],
        ],
        'externe' => [
            'label' => 'Externe',
            'description' => 'Personne sans profil pensionné titulaire (représentant, mandataire, admin technique, etc.).',
            'is_system' => true,
            'capabilities' => [
                'portal_home' => 'externe',
                'requires_beneficiaire' => false,
                'can_hold_service' => false,
            ],
            'default_roles' => [],
        ],
    ],

];
