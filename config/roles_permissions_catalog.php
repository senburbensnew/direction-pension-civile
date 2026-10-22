<?php

/*
| Libellés descriptifs des rôles et permissions Spatie (colonne description).
| Synchronisés par RolesAndPermissionsSeeder.
*/

return [

    'roles' => [
        'admin' => 'Administrateur technique : accès complet à l’interface d’administration.',
        'agent_formalites' => 'Agent du service accueil et formalités : traitement des dossiers et demandes de compte.',
        'agent_rdv' => 'Agent chargé de la planification et du suivi des rendez-vous.',
        'assistant_directeur' => 'Assistant(e) de direction : supervision et corbeille interne.',
        'directeur' => 'Directeur de la Direction de la pension civile : vision globale et validation.',
        'fonctionnaire' => 'Fonctionnaire actif utilisant le portail pour ses démarches de pension.',
        'institution' => 'Représentant d’une institution adhérente au régime.',
        'pensionne' => 'Pensionné ou ayant droit accédant au portail usager.',
        'responsable_service_accueil_formalites' => 'Responsable du service accueil et formalités.',
        'validateur_rdv' => 'Valide ou supervise les demandes de rendez-vous.',
    ],

    'permissions' => [
        'can_view_pensionne_section' => 'Voir la section dédiée aux pensionnés sur le site public.',
        'can_view_fonctionnaire_section' => 'Voir la section dédiée aux fonctionnaires sur le site public.',
        'can_view_institution_section' => 'Voir la section dédiée aux institutions sur le site public.',
        'can_view_pensionne_menu' => 'Accéder au menu et aux parcours pensionné après connexion.',
        'can_view_fonctionnaire_menu' => 'Accéder au menu et aux parcours fonctionnaire après connexion.',
        'can_view_institution_menu' => 'Accéder au menu et aux parcours institution après connexion.',
        'can_view_dashboard' => 'Accéder au tableau de bord du portail utilisateur.',

        'pension_mandats.view' => 'Consulter les demandes de mandat de pension.',
        'pension_mandats.approuver' => 'Approuver une demande de mandat de pension.',
        'pension_mandats.revoquer' => 'Révoquer un mandat de pension existant.',

        'beneficiaire_relations.view' => 'Consulter les demandes de lien bénéficiaire.',
        'beneficiaire_relations.approuver' => 'Approuver une demande de lien bénéficiaire.',
        'beneficiaire_relations.revoquer' => 'Révoquer un lien bénéficiaire approuvé.',

        'newsletter.view' => 'Consulter la newsletter et les campagnes.',
        'newsletter.manage_subscribers' => 'Gérer la liste des abonnés à la newsletter.',
        'newsletter.manage_campaigns' => 'Créer et modifier les campagnes newsletter.',
        'newsletter.send' => 'Envoyer une campagne newsletter.',

        'nav.mes_demandes' => 'Afficher le lien « Mes demandes » dans le menu du portail.',
        'nav.corbeille' => 'Afficher le lien « Corbeille » pour le traitement interne des dossiers.',
        'nav.beneficiaires_mandats' => 'Afficher « Mes bénéficiaires / Mes mandats » dans le menu.',
        'nav.formalites_comptes' => 'Afficher « Comptes (formalités) » pour les demandes de création de compte.',
        'nav.formalites_suivi' => 'Afficher « Suivi formalités » pour le suivi des dossiers formalités.',
    ],

];
