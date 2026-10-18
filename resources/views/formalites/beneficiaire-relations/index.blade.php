<x-app-layout>
    <x-portal-page bottom="pb-6" class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-indigo-600">
            <p class="dossier-kicker">Formalités</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Validation des relations représentant</h1>
            <p class="mt-2 text-sm text-gray-600">
                Examen des demandes de lien représentant et gestion des accès actifs.
            </p>
        </header>

        @include('formalites.beneficiaire-relations._active_links', ['activeLinks' => $activeLinks])
        @include('formalites.beneficiaire-relations._list', ['demandes' => $demandes])
    </x-portal-page>
</x-app-layout>
