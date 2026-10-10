<x-app-layout>

<div class="max-w-6xl mx-auto px-4 py-8">
    <nav class="flex items-center gap-1.5 text-xs text-gray-400 mb-4" aria-label="Fil d'Ariane">
        <a href="{{ route('personal.cart') }}" class="hover:text-gray-600 transition-colors">Corbeille</a>
        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route($routePrefix ?? 'formalites').'.comptes-demandes.index' }}" class="hover:text-gray-600 transition-colors">Création de compte</a>
        <svg class="w-3 h-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-600 font-medium truncate" aria-current="page">{{ $demande->code ?? $demande->libellePublic() }}</span>
    </nav>

    <div class="space-y-6">
        @include('comptes-demandes._detail')
    </div>
</div>
</x-app-layout>
