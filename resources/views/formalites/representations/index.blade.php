<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Formalités — mandats et relations</h2>
    </x-slot>

    <x-portal-page class="max-w-6xl space-y-4">
        <div class="flex gap-2 border-b border-gray-200">
            <a href="{{ route('formalites.representations.index', ['tab' => 'mandats']) }}"
               class="px-4 py-2 text-sm font-medium {{ $tab === 'mandats' ? 'border-b-2 border-indigo-600 text-indigo-700' : 'text-gray-600' }}">
                Mandats pension
            </a>
            <a href="{{ route('formalites.representations.index', ['tab' => 'relations']) }}"
               class="px-4 py-2 text-sm font-medium {{ $tab === 'relations' ? 'border-b-2 border-indigo-600 text-indigo-700' : 'text-gray-600' }}">
                Relations représentant
            </a>
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
            ])
        @else
            @include('admin.pension-mandats._list', [
                'mandats' => $mandats,
                'demandeTypeChoices' => $demandeTypeChoices,
                'approveRoute' => 'formalites.pension-mandats.approve',
                'refuseRoute' => 'formalites.pension-mandats.refuse',
                'revokeRoute' => 'formalites.pension-mandats.revoke',
                'pieceRoute' => 'formalites.pension-mandats.piece',
            ])
        @endif
    </x-portal-page>
</x-app-layout>
