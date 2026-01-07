@extends('layouts.main')

@section('content')
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Demande d'Attestation</h2>

        <form action="{{ route('demandes.attestations.store') }}" method="POST">
            @csrf

            {{-- Code pension --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Code pension</label>
                <input
                    type="text"
                    name="pensioner_code"
                    value="{{ old('pensioner_code', auth()->user()->pension_code) }}"
                    class="mt-1 block w-full rounded-md border
                        @error('pensioner_code') border-red-500 @else border-gray-300 @enderror"
                >
                @error('pensioner_code')
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
                    class="mt-1 block w-full rounded-md border
                        @error('nif') border-red-500 @else border-gray-300 @enderror"
                >
                @error('nif')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nom --}}
            <div class="mb-4">
                <label for="lastname" class="block text-sm font-medium text-gray-700">Nom</label>
                <input
                    type="text"
                    id="lastname"
                    name="lastname"
                    value="{{ old('lastname', auth()->user()->lastname ?? auth()->user()->name) }}"
                    class="mt-1 block w-full px-4 py-2 rounded-md shadow-sm border
                        @error('lastname') border-red-500 @else border-gray-300 @enderror
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('lastname')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Prénom --}}
            <div class="mb-4">
                <label for="firstname" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input
                    type="text"
                    id="firstname"
                    name="firstname"
                    value="{{ old('firstname', auth()->user()->firstname ?? auth()->user()->name) }}"
                    class="mt-1 block w-full px-4 py-2 rounded-md shadow-sm border
                        @error('firstname') border-red-500 @else border-gray-300 @enderror
                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('firstname')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Soumettre la demande
                </button>
            </div>
        </form>
    </div>
@endsection
