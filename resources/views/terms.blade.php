@extends('layouts.main')

@section('title', 'Conditions d’utilisation')

@section('content')
<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

@php
    $sections = [
        [
            'icon' => 'fa-circle-check',
            'title' => '1. Objet',
            'content' => 'Les présentes conditions régissent l’accès et l’utilisation du site et des services en ligne de la Direction de la Pension Civile.',
            'list' => [],
        ],
        [
            'icon' => 'fa-user-check',
            'title' => '2. Compte utilisateur',
            'content' => 'La création d’un compte est soumise à validation. L’utilisateur s’engage à :',
            'list' => [
                'Fournir des informations exactes et à jour',
                'Préserver la confidentialité de ses identifiants',
                'Utiliser le compte uniquement pour des démarches légitimes auprès de la DPC',
            ],
        ],
        [
            'icon' => 'fa-ban',
            'title' => '3. Usages interdits',
            'content' => 'Il est interdit d’utiliser la plateforme pour :',
            'list' => [
                'Usurper l’identité d’un tiers',
                'Altérer, extraire ou détourner des données',
                'Perturber le fonctionnement des services',
            ],
        ],
        [
            'icon' => 'fa-scale-balanced',
            'title' => '4. Responsabilités',
            'content' => 'La DPC met tout en œuvre pour assurer la disponibilité du service. Elle ne saurait être tenue responsable des interruptions indépendantes de sa volonté, ni de l’usage abusif du compte par l’utilisateur.',
            'list' => [],
        ],
        [
            'icon' => 'fa-file-pen',
            'title' => '5. Modifications',
            'content' => 'Les conditions d’utilisation peuvent être mises à jour. La version en vigueur est celle publiée sur cette page.',
            'list' => [],
        ],
    ];
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Informations légales</span>
            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">Conditions d’utilisation</h1>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Règles applicables à l’utilisation des services en ligne de la Direction de la Pension Civile.
            </p>
        </div>

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
    </div>
</div>
@endsection
