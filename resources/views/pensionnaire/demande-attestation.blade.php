@extends('layouts.main')

@section('content')
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
            <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-around items-center mb-12 gap-8">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
                <h1 class="text-xl md:text-2xl font-bold text-center">
                    MINISTERE DE L’ECONOMIE ET DES FINANCES<br>
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

        <form action="{{ route('demandes.attestations.store') }}" method="POST">
            @csrf

            {{-- Code pension --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Code pension</label>
                <input
                    type="text"
                    name="code_pension"
                    value="{{ old('code_pension', auth()->user()->pension_code) }}"
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
                    value="{{ old('nif', auth()->user()->nif) }}"
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
                    value="{{ old('nom', auth()->user()->lastname ?? auth()->user()->name) }}"
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
                    value="{{ old('prenom', auth()->user()->firstname ?? auth()->user()->name) }}"
                    class="mt-1 block w-full px-4 py-2 rounded-md shadow-sm border
                        @error('prenom') border-red-500 @else border-gray-300 @enderror
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('prenom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- DECLARATION -->
            <fieldset class="mb-6 p-5 border rounded-lg">
                <label class="flex items-start">
                    <input type="checkbox" name="consentement" value="1">
                    <span class="ml-2 text-sm">Je certifie l’exactitude des informations</span>
                </label>
                @error('consentement')
                    <p class="text-red-600 text-sm mt-2">{{ $message }}</p>
                @enderror
            </fieldset>

            <div class="mt-6 text-right">
                <button type="submit"
                    class=" bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Soumettre
                </button>
            </div>
        </form>
    </div>
@endsection
