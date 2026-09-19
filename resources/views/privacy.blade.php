@extends('layouts.main')

@section('title', 'Politique de Confidentialité et de la Protection des Données')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

@php
    $sections = [
        [
            'icon' => 'fa-id-card',
            'title' => '1. Collecte des informations',
            'content' => 'Nous collectons uniquement les informations nécessaires pour fournir nos services, telles que :',
            'list' => ['Nom, prénom et coordonnées', 'Informations relatives aux formulaires administratifs', 'Adresses e-mail pour la correspondance et les notifications'],
        ],
        [
            'icon' => 'fa-gears',
            'title' => '2. Utilisation des informations',
            'content' => 'Les informations collectées sont utilisées pour :',
            'list' => ['Traiter les demandes des utilisateurs', 'Améliorer nos services et notre site web', 'Envoyer des communications importantes relatives aux services proposés'],
        ],
        [
            'icon' => 'fa-share-nodes',
            'title' => '3. Partage des informations',
            'content' => 'Nous ne vendons ni ne louons vos informations personnelles à des tiers. Les informations peuvent être partagées uniquement avec :',
            'list' => ['Les partenaires autorisés pour la gestion des services', 'Les autorités compétentes lorsque la loi l’exige'],
        ],
        [
            'icon' => 'fa-shield-halved',
            'title' => '4. Sécurité des données',
            'content' => 'Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles pour protéger vos données contre tout accès non autorisé, perte ou divulgation.',
            'list' => [],
        ],
        [
            'icon' => 'fa-user-shield',
            'title' => '5. Droits des utilisateurs',
            'content' => 'Conformément à la législation applicable, les utilisateurs disposent des droits suivants :',
            'list' => ['Accéder à leurs données personnelles', 'Demander la correction ou la suppression de leurs données', 'Retirer leur consentement à tout moment pour certaines utilisations'],
        ],
        [
            'icon' => 'fa-file-pen',
            'title' => '6. Modifications de la politique',
            'content' => 'Nous pouvons mettre à jour cette politique de confidentialité de temps en temps. Toute modification sera publiée sur cette page avec la date de mise à jour.',
            'list' => [],
        ],
    ];
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">

        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Informations légales</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Politique de confidentialité</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Protection des données personnelles des utilisateurs de la Direction de la Pension Civile.
            </p>
        </div>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-4">
                <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Notre engagement</h2>
            </div>
            <p class="text-gray-700 leading-relaxed">
                La Direction de la Pension Civile s'engage à protéger la vie privée et les données personnelles de ses utilisateurs.
                La présente politique explique quelles informations nous collectons, comment elles sont utilisées et les choix dont disposent nos utilisateurs.
            </p>
        </section>

        @foreach($sections as $sec)
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="text-3xl text-navy flex items-center justify-center shrink-0">
                        <i class="fa-solid {{ $sec['icon'] }}" aria-hidden="true"></i>
                    </span>
                    <h2 class="text-xl font-bold text-navy">{{ $sec['title'] }}</h2>
                </div>
                <p class="text-gray-700 leading-relaxed {{ count($sec['list']) ? 'mb-4' : '' }}">{{ $sec['content'] }}</p>
                @if(count($sec['list']))
                    <ul class="space-y-2.5 text-gray-700 text-sm leading-relaxed">
                        @foreach($sec['list'] as $item)
                            <li class="flex items-start gap-2.5">
                                <i class="fa-solid fa-check text-navy mt-1 text-xs" aria-hidden="true"></i>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach

        <p class="text-sm text-gray-500 text-center">
            Date de dernière mise à jour : 30 novembre 2025
        </p>

    </div>
</div>
@endsection
