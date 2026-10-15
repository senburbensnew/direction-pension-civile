@php
    $tabLabels = [
        'mandats' => 'Mandats pension',
        'relations' => 'Relations représentant',
    ];
    $activeTabLabel = $tabLabels[$tab] ?? $tab;
    $resultCount = $tab === 'relations'
        ? $relationDemandes->total()
        : $mandats->total();
@endphp

<x-app-layout>
    <x-portal-page bottom="pb-6" class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-indigo-600">
            <p class="dossier-kicker">Formalités</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Mandats et relations</h1>
            <p class="mt-2 text-sm text-gray-600 max-w-2xl">
                Validation des mandataires pension et des liens représentant (tuteur, curateur, etc.).
            </p>
        </header>

        @if(session('success'))
            <div class="dossier-notice border-l-emerald-600 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="dossier-stat">
                <p class="dossier-stat__label">Vue</p>
                <p class="dossier-stat__value text-base">{{ $activeTabLabel }}</p>
            </div>
            <div class="dossier-stat border-l-4 border-l-[#173052]">
                <p class="dossier-stat__label">Résultats</p>
                <p class="dossier-stat__value text-2xl">{{ $resultCount }}</p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach($tabLabels as $key => $label)
                <a href="{{ route('formalites.representations.index', ['tab' => $key]) }}"
                   @class([
                       'dossier-filter',
                       'dossier-filter--active' => $tab === $key,
                       'dossier-filter--idle' => $tab !== $key,
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        @if($tab === 'relations')
            @include('formalites.beneficiaire-relations._active_links', [
                'activeLinks' => $activeRepresentativeLinks,
            ])
            @include('formalites.beneficiaire-relations._list', [
                'demandes' => $relationDemandes,
                'approveRoute' => 'formalites.beneficiaire-relations.approve',
                'refuseRoute' => 'formalites.beneficiaire-relations.refuse',
                'pieceRoute' => 'formalites.beneficiaire-relations.piece',
                'listTitle' => 'Demandes de relation à traiter',
            ])
        @else
            @include('admin.pension-mandats._list', [
                'mandats' => $mandats,
                'demandeTypeChoices' => $demandeTypeChoices,
                'approveRoute' => 'formalites.pension-mandats.approve',
                'refuseRoute' => 'formalites.pension-mandats.refuse',
                'revokeRoute' => 'formalites.pension-mandats.revoke',
                'pieceRoute' => 'formalites.pension-mandats.piece',
                'listTitle' => 'Mandats pension',
            ])
        @endif
    </x-portal-page>
</x-app-layout>
