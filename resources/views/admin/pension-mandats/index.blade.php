<x-app-layout>
    <x-portal-page bottom="pb-6" class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-indigo-600">
            <p class="dossier-kicker">Formalités</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Validation des mandats pension</h1>
            <p class="mt-2 text-sm text-gray-600">
                Approbation des mandataires et périmètre des demandes autorisées.
            </p>
        </header>

        @include('admin.pension-mandats._list', compact('mandats', 'demandeTypeChoices'))
    </x-portal-page>
</x-app-layout>
