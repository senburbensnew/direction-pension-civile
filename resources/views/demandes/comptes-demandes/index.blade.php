<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Demandes de création de compte
            </h2>
            <p class="text-sm text-gray-500 mt-0.5 hidden sm:block">
                Examen et décision des demandes d’ouverture de compte en ligne
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        @include('comptes-demandes._list')
    </div>
</x-app-layout>
