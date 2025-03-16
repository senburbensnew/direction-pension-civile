@extends('layouts.main')

@section('content')
    {{--     <div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <!-- Breadcrumb -->
        <nav class="text-sm text-gray-600 mb-4">
            <span class="text-gray-800">Fonctionnaire</span>
            <span class="mx-2">></span>
            <span class="text-gray-800">Simulation de retraite</span>
        </nav>

        <!-- Page Title & Download Button -->
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Simulation de retraite</h1>
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
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Simulation de Retraite</h2>

        <form action="" method="POST">
            @csrf

            <!-- Age -->
            <div class="mb-4">
                <label for="age" class="block text-sm font-medium text-gray-700">Âge</label>
                <input type="number" id="age" name="age" value="{{ old('age') }}" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('age')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Years Worked -->
            <div class="mb-4">
                <label for="years_worked" class="block text-sm font-medium text-gray-700">Nombre d'années
                    travaillées</label>
                <input type="number" id="years_worked" name="years_worked" value="{{ old('years_worked') }}" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('years_worked')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Salary -->
            <div class="mb-4">
                <label for="salary" class="block text-sm font-medium text-gray-700">Salaire mensuel (en HTG)</label>
                <input type="number" id="salary" name="salary" value="{{ old('salary') }}" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('salary')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Pension Estimation -->
            <div class="mb-4">
                <label for="pension_estimation" class="block text-sm font-medium text-gray-700">Estimation de la pension (en
                    HTG)</label>
                <input type="number" id="pension_estimation" name="pension_estimation"
                    value="{{ old('pension_estimation') }}" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('pension_estimation')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="mt-6">
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Simuler la retraite
                </button>
            </div>
        </form>
    </div>
@endsection
