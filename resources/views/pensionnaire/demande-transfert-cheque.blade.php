@extends('layouts.main')

@section('content')
    {{--     <div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Pensionnaire</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Demande de transfert de cheques</span>
        </nav>

        <!-- Page Title & Download Button -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Demande de transfert de cheques</h1>
            <a href="{{ asset('documents/guide-planaffaires.pdf') }}" download
                class="inline-block px-4 py-2 bg-blue-600 text-white font-medium text-sm rounded hover:bg-blue-700 focus:outline-none focus:ring focus:ring-blue-300">
                Télécharger
            </a>
        </div>

        <!-- PDF Viewer -->
        <div class="border border-gray-300 rounded-lg overflow-hidden">
            <embed src="{{ asset('documents/guide-planaffaires.pdf') }}" type="application/pdf" width="100%"
                height="600px" class="block">
        </div>
    </div> --}}
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Demande de Transfert de Chèque</h2>

        <form action="{{ route('pensionnaire.process-check-transfer-request') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="firstname" class="block text-sm font-medium text-gray-700">Prénom</label>
                <input type="text" id="firstname" name="firstname" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="lastname" class="block text-sm font-medium text-gray-700">Nom</label>
                <input type="text" id="lastname" name="lastname" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div class="mb-4">
                <label for="amount" class="block text-sm font-medium text-gray-700">Montant</label>
                <input type="number" id="amount" name="amount" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
