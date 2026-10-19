<?php

use Database\Seeders\RolesAndPermissionsSeeder;

/*
| Entrées de menu portail / compte — visibilité via permissions Spatie (nav.*).
| Ajustables par rôle dans Admin → Rôles (cases à cocher, comme les autres permissions).
*/

return [

    'permissions' => [
        'nav.mes_demandes' => 'Mes demandes',
        'nav.corbeille' => 'Corbeille',
        'nav.beneficiaires_mandats' => 'Mes bénéficiaires / Mes mandats',
        'nav.formalites_comptes' => 'Comptes (formalités)',
        'nav.formalites_suivi' => 'Suivi formalités',
    ],

    'defaults_by_role' => [
        'pensionne' => [
            'nav.mes_demandes',
        ],
        'fonctionnaire' => [
            'nav.mes_demandes',
        ],
        'institution' => [
            'nav.mes_demandes',
        ],
        'agent_formalites' => [
            'nav.corbeille',
        ],
        'agent_rdv' => [
            'nav.corbeille',
        ],
        'responsable_service_accueil_formalites' => [
            'nav.corbeille',
            'nav.formalites_suivi',
        ],
        'validateur_rdv' => [
            'nav.corbeille',
            'nav.formalites_suivi',
        ],
        'directeur' => [
            'nav.corbeille',
            'nav.formalites_suivi',
        ],
        'assistant_directeur' => [
            'nav.corbeille',
            'nav.formalites_suivi',
        ],
        'admin' => [],
    ],

    'internal_roles_for_corbeille' => RolesAndPermissionsSeeder::internalRoles(),

];
