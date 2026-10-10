<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Rôles Spatie canoniques (fonctions DPC)
    |--------------------------------------------------------------------------
    */

    'canonical' => [
        'admin',
        'directeur',
        'assistant_directeur',
        'agent_rdv',
        'validateur_rdv',
        'agent_formalites',
        'pensionne',
        'fonctionnaire',
        'institution',
        'responsable_service_accueil_formalites',
        'responsable_service_controle_placement',
        'responsable_service_comptabilite',
        'responsable_service_assurance',
        'responsable_secretariat',
    ],

    /*
    |--------------------------------------------------------------------------
    | Anciens noms → rôle canonique (migration model_has_roles)
    |--------------------------------------------------------------------------
    */

    'legacy_aliases' => [
        'direction' => 'directeur',
        'responsable_direction' => 'directeur',
        'responsable_directeur' => 'directeur',
        'responsable_assistant_directeur' => 'assistant_directeur',
        'service_liquidation' => null,
        'service_accueil_formalites' => 'agent_formalites',
        'service_controle_placement' => null,
        'service_comptabilite' => null,
        'service_assurance' => null,
        'service_formalite' => 'agent_formalites',
        'administration' => null,
        'responsable_administration' => null,
        'secretariat' => 'responsable_secretariat',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exige un service_id à l'assignation (validation admin)
    |--------------------------------------------------------------------------
    */

    'requires_service' => [
        'agent_rdv',
        'validateur_rdv',
        'agent_formalites',
        'responsable_service_accueil_formalites',
        'responsable_service_controle_placement',
        'responsable_service_comptabilite',
        'responsable_service_assurance',
        'responsable_secretariat',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rôles considérés comme « portail » (liés aux catégories)
    |--------------------------------------------------------------------------
    */

    'portal_roles' => [
        'pensionne',
        'fonctionnaire',
        'institution',
    ],

];
