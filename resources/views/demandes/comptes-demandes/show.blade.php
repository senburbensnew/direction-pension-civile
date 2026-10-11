<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight truncate">
                Demande de compte
            </h2>
            <p class="text-sm text-gray-500 mt-0.5 hidden sm:block truncate">
                {{ $demande->libellePublic() }}
            </p>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
        <nav class="text-sm text-gray-500 flex flex-wrap items-center gap-x-2 gap-y-1" aria-label="Fil d'Ariane">
            <a href="{{ route('personal.cart') }}" class="hover:text-[#173052] transition-colors">Corbeille</a>
            <span class="text-gray-300" aria-hidden="true">/</span>
            <a href="{{ route($routePrefix ?? 'formalites').'.comptes-demandes.index' }}"
               class="hover:text-[#173052] transition-colors">Création de compte</a>
            <span class="text-gray-300" aria-hidden="true">/</span>
            <span class="text-gray-700 font-medium truncate">{{ $demande->code ?? $demande->libellePublic() }}</span>
        </nav>

        @include('comptes-demandes._detail')
    </div>
</x-app-layout>
