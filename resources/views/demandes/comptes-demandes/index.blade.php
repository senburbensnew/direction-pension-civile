<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Création de compte</h2>
    </x-slot>

    <x-portal-page bottom="pb-6" class="space-y-6">
        <header class="dossier-header px-5 py-5 sm:px-6 border-l-4 border-l-indigo-600">
            <p class="dossier-kicker">Formalités</p>
            <h1 class="mt-1 text-xl font-semibold text-[#173052] tracking-tight">Demandes de création de compte</h1>
            <p class="mt-2 text-sm text-gray-600">
                Examen des pièces et décision d’ouverture de compte en ligne.
            </p>
        </header>
        @include('comptes-demandes._list')
    </x-portal-page>
</x-app-layout>
