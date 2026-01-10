@extends('layouts.main')

@section('content')
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
                    <!-- HEADER -->
            <div class="flex flex-col md:flex-row justify-around items-center mb-12 gap-8">
                <img src="{{ asset('images/setting-logo-1-M13oPLiYoM.png') }}" class="w-24 h-24 object-cover">
                <h1 class="text-xl md:text-2xl font-bold text-center">
                    MINISTERE DE L’ECONOMIE ET DES FINANCES<br>
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

        <form action="{{ route('demandes.demande-reinsertion.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="prenom" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" id="prenom" name="prenom"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('prenom') border-red-500 @else border-gray-300 @enderror">
                @error('prenom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="nom" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="nom" name="nom"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nom') border-red-500 @else border-gray-300 @enderror">
                @error('nom')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="raison" class="block text-sm font-medium text-gray-700">Motif de réinsertion</label>
                <textarea id="raison" name="raison" rows="4" 
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('raison') border-red-500 @else border-gray-300 @enderror"></textarea>
                @error('raison')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 text-right">
                <button type="submit"
                    class="bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Soumettre
                </button>
            </div>
        </form>
    </div>
@endsection
