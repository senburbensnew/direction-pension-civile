@extends('layouts.main')

@section('title', 'Accueil')

@section('css')
@endsection

@section('content')
    {{--     <div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Fonctionnaire</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Demande d'etat de carriere</span>
        </nav>

        <!-- Page Title & Download Button -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Demande d'etat de carriere</h1>
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
    <!-- resources/views/career-state-form.blade.php -->
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Demande d’état de carrière</h2>

        <form action="#" method="POST">
            @csrf
            <!-- Personal Information -->
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
                <label for="position" class="block text-sm font-medium text-gray-700">Poste</label>
                <input type="text" id="position" name="position" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Date of Joining -->
            <div class="mb-4">
                <label for="joining_date" class="block text-sm font-medium text-gray-700">Date d'entrée</label>
                <input type="date" id="joining_date" name="joining_date" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Department Information -->
            <div class="mb-4">
                <label for="department" class="block text-sm font-medium text-gray-700">Département</label>
                <select id="department" name="department" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Sélectionner un département</option>
                    <option value="finance">Finance</option>
                    <option value="human_resources">Ressources Humaines</option>
                    <option value="it">Informatique</option>
                    <option value="marketing">Marketing</option>
                </select>
            </div>

            <!-- Comments -->
            <div class="mb-4">
                <label for="comments" class="block text-sm font-medium text-gray-700">Commentaires</label>
                <textarea id="comments" name="comments" rows="4"
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <!-- Submit Button -->
            <div class="mt-6">
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Soumettre la demande
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
@endsection
