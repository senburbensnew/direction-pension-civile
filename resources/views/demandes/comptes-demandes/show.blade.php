<x-app-layout>

<div class="max-w-6xl mx-auto px-4 py-8 space-y-6">
    <nav class="text-sm text-gray-500 flex items-center mb-5" aria-label="Fil d'Ariane">
        <a href="{{ route('personal.cart') }}" class="hover:underline text-blue-600">Corbeille</a>
        <span class="mx-2" aria-hidden="true">/</span>
        <a href="{{ route('personal.demandes.index') }}" class="hover:underline text-blue-600">
            Demande de création de compte
        </a>
        <span class="mx-2" aria-hidden="true">/</span>
        <span class="text-gray-800 font-medium" aria-current="page">{{ $demande->code }}</span>
    </nav>
    @include('comptes-demandes._detail')
</div>
</x-app-layout>
