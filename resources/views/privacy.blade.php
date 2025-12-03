@extends('layouts.main')

@section('title', 'Politique de Confidentialité et de la Protection des Données')

@section('content')

<style>
    .gradient-text {
        background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .card-shadow {
        box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04);
    }
    .fade-in {
        animation: fadeIn 0.6s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="py-8 bg-gray-50 fade-in">
    <div class="max-w-7xl mx-auto px-6">
        <div class="bg-white rounded-2xl card-shadow overflow-hidden p-10">
            <h1 class="text-3xl font-bold mb-6 text-center gradient-text">
                Politique de Confidentialité et de la Protection des Données des Utilisateurs
            </h1>
        <p class="mb-6 text-gray-700">
            La Direction de la Pension Civile s'engage à protéger la vie privée et les données personnelles de ses utilisateurs.
            La présente politique explique quelles informations nous collectons, comment elles sont utilisées et les choix dont disposent nos utilisateurs.
        </p>

        <!-- Sections -->
        @php
            $sections = [
                [
                    'title' => '1. Collecte des informations',
                    'content' => 'Nous collectons uniquement les informations nécessaires pour fournir nos services, telles que :',
                    'list' => ['Nom, prénom et coordonnées', 'Informations relatives aux formulaires administratifs', 'Adresses e-mail pour la correspondance et les notifications']
                ],
                [
                    'title' => '2. Utilisation des informations',
                    'content' => 'Les informations collectées sont utilisées pour :',
                    'list' => ['Traiter les demandes des utilisateurs', 'Améliorer nos services et notre site web', 'Envoyer des communications importantes relatives aux services proposés']
                ],
                [
                    'title' => '3. Partage des informations',
                    'content' => 'Nous ne vendons ni ne louons vos informations personnelles à des tiers. Les informations peuvent être partagées uniquement avec :',
                    'list' => ['Les partenaires autorisés pour la gestion des services', 'Les autorités compétentes lorsque la loi l’exige']
                ],
                [
                    'title' => '4. Sécurité des données',
                    'content' => 'Nous mettons en œuvre des mesures de sécurité techniques et organisationnelles pour protéger vos données contre tout accès non autorisé, perte ou divulgation.',
                    'list' => []
                ],
                [
                    'title' => '5. Droits des utilisateurs',
                    'content' => 'Conformément à la législation applicable, les utilisateurs disposent des droits suivants :',
                    'list' => ['Accéder à leurs données personnelles', 'Demander la correction ou la suppression de leurs données', 'Retirer leur consentement à tout moment pour certaines utilisations']
                ],
                [
                    'title' => '6. Modifications de la politique',
                    'content' => 'Nous pouvons mettre à jour cette politique de confidentialité de temps en temps. Toute modification sera publiée sur cette page avec la date de mise à jour.',
                    'list' => []
                ]
            ];
        @endphp

        @foreach($sections as $sec)
            <div class="mb-8">
                <h2 class="text-2xl font-semibold mb-3 gradient-text">{{ $sec['title'] }}</h2>
                <p class="mb-4 text-gray-700">{{ $sec['content'] }}</p>
                @if(count($sec['list']) > 0)
                    <ul class="list-disc ml-6 text-gray-700">
                        @foreach($sec['list'] as $item)
                            <li class="mb-1">{{ $item }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach

        <p class="mt-6 text-sm text-gray-500 text-center">
            Date de dernière mise à jour : 30 novembre 2025
        </p>
    </div>
</div>
</div>
@endsection
