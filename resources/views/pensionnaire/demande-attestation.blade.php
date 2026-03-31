@extends('layouts.main')

@section('content')
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-around items-center mb-12 gap-8">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
                <h1 class="text-xl md:text-2xl font-bold text-center">
                    MINISTERE DE L'ECONOMIE ET DES FINANCES<br>
                    <span class="underline">PENSION CIVILE</span><br>
                    Demande d'Attestation
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

        <form action="{{ route('demandes.attestations.store') }}" method="POST" id="main-form">
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
                    placeholder="ex : Attestation de pension — Mars 2026"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                >
            </div>

            {{-- Code pension --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Code pension</label>
                <input
                    type="text"
                    name="code_pension"
                    value="{{ old('code_pension', $demande?->data['code_pension'] ?? auth()->user()->pension_code) }}"
                    class="mt-1 block w-full rounded-md border
                        @error('code_pension') border-red-500 @else border-gray-300 @enderror"
                >
                @error('code_pension')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- NIF --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">NIF</label>
                <input
                    type="text"
                    name="nif"
                    value="{{ old('nif', $demande?->data['nif'] ?? auth()->user()->nif) }}"
                    placeholder="998-369-226-3"
                    class="mt-1 block w-full rounded-md border
                        @error('nif') border-red-500 @else border-gray-300 @enderror"
                >
                @error('nif')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nom --}}
            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input
                    type="text"
                    id="nom"
                    name="nom"
                    value="{{ old('nom', $demande?->data['nom'] ?? auth()->user()->lastname ?? auth()->user()->name) }}"
                    class="mt-1 block w-full px-4 py-2 rounded-md shadow-sm border
                        @error('nom') border-red-500 @else border-gray-300 @enderror
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('nom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Prénom --}}
            <div class="mb-4">
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input
                    type="text"
                    id="prenom"
                    name="prenom"
                    value="{{ old('prenom', $demande?->data['prenom'] ?? auth()->user()->firstname ?? auth()->user()->name) }}"
                    class="mt-1 block w-full px-4 py-2 rounded-md shadow-sm border
                        @error('prenom') border-red-500 @else border-gray-300 @enderror
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('prenom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <button type="button"
                    onclick="document.getElementById('action-input').value='draft'; document.getElementById('main-form').submit();"
                    class="bg-gray-200 text-gray-700 px-6 py-2 rounded hover:bg-gray-300">
                    Sauvegarder
                </button>
                <button type="button"
                    onclick="document.getElementById('action-input').value='submit'; document.getElementById('main-form').submit();"
                    class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                    Soumettre
                </button>
            </div>
        </form>
    </div>
@endsection
