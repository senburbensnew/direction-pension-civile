@extends('layouts.main')

@section('content')
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
                    <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-around items-center mb-12 gap-8">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
                <h1 class="text-xl md:text-2xl font-bold text-center">
                    MINISTERE DE L'ECONOMIE ET DES FINANCES<br>
                    <span class="underline">PENSION CIVILE</span><br>
                    Demande de Réinsertion
                </h1>
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
            </div>

        @if (session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-700 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if ($demande)
            <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded text-sm">
                Brouillon en cours — dernière sauvegarde {{ $demande->updated_at->diffForHumans() }}
            </div>
        @endif

        <form action="{{ route('demandes.demande-reinsertion.store') }}" method="POST" id="main-form">
            @csrf
            <input type="hidden" name="demande_id" value="{{ $demande?->id }}">
            <input type="hidden" name="action" id="action-input" value="draft">

            {{-- Titre personnalisé --}}
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">
                    Titre personnalisé <span class="text-gray-400 font-normal">(optionnel)</span>
                </label>
                <input
                    id="title"
                    type="text"
                    name="title"
                    value="{{ old('title', $demande?->title ?? '') }}"
                    placeholder="ex : Demande de réinsertion — 2026"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            <div class="mb-4">
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" id="prenom" name="prenom"
                    value="{{ old('prenom', $demande?->data['prenom'] ?? '') }}"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('prenom') border-red-500 @else border-gray-300 @enderror">
                @error('prenom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="nom" name="nom"
                    value="{{ old('nom', $demande?->data['nom'] ?? '') }}"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nom') border-red-500 @else border-gray-300 @enderror">
                @error('nom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="raison" class="block text-sm font-medium text-gray-700">Motif de réinsertion</label>
                <textarea id="raison" name="raison" rows="4"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('raison') border-red-500 @else border-gray-300 @enderror">{{ old('raison', $demande?->data['raison'] ?? '') }}</textarea>
                @error('raison')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('action-input').value='draft'; document.getElementById('main-form').submit();"
                    class="bg-gray-200 text-gray-700 py-2 px-4 rounded-md shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400">
                    Sauvegarder en brouillon
                </button>
                <button type="button"
                    onclick="document.getElementById('action-input').value='submit'; document.getElementById('main-form').submit();"
                    @if($demande && $isDemandeReadyForSubmission) class="bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @else class="bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500" @endif>
                    Soumettre
                </button>
            </div>
        </form>
    </div>
@endsection
