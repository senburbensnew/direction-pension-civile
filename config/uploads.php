<?php

use App\Models\BeneficiaireRelationDemande;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\PensionMandat;

return [

    /*
    |--------------------------------------------------------------------------
    | Disques par domaine
    |--------------------------------------------------------------------------
    |
    | public  → storage/app/public (symlink /storage)
    | private → storage/app/private (documents sensibles, hors web)
    |
    */

    'disks' => [
        'public' => env('UPLOADS_PUBLIC_DISK', 'public'),
        'private' => env('UPLOADS_PRIVATE_DISK', 'private'),
        'demandes' => env('UPLOADS_DEMANDES_DISK', env('DEMANDES_DISK', 'private')),
        'comptes' => env('UPLOADS_COMPTES_DISK', 'private'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Arborescence storage/app/public
    |--------------------------------------------------------------------------
    |
    | Voir documentation/storage-arborescence.md
    |
    | Médiathèque : cms/mediatheque/{image|video|audio|document}/  (singulier)
    | Rapports    : cms/reports/{year}/
    |
    */

    'paths' => [
        'cms' => [
            'actualites' => 'cms/actualites',
            'carousel' => 'cms/carousel',
            'mediatheque' => 'cms/mediatheque',
            'publications' => 'cms/publications',
            'reports' => 'cms/reports',
            'partenaires' => 'cms/partenaires',
            'officials' => 'cms/officials',
            'institution' => 'cms/institution',
        ],

        'mediatheque_types' => ['image', 'video', 'audio', 'document'],
        'users' => [
            'profile_photo' => 'users/profile-photos',
        ],
        'demandes' => [
            /** Fichiers référencés dans demandes.data (hors Media Library) */
            'embedded' => 'demandes/embedded/{type}/{year}/{month}',
        ],
        'temp' => [
            'form_stash' => 'temp/form-stash',
        ],
        'secrets' => 'secrets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Spatie Media Library — dossiers par modèle
    |--------------------------------------------------------------------------
    |
    | Résultat : media/{dossier}/{model_id}/{collection}/{media_id}/
    |
    */

    'media_folders' => [
        Demande::class => 'demandes',
        DemandeCreationCompte::class => 'comptes',
        BeneficiaireRelationDemande::class => 'beneficiaire-relations',
        PensionMandat::class => 'mandats',
    ],

];
