@extends('layouts.main')

@section('content')
    <div class="m-5 max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Demande de Pension</h2>

        <form action="{{ route('fonctionnaire.process-pension-request') }}" method="POST">
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

            <!-- Position -->
            <div class="mb-4">
                <label for="position" class="block text-sm font-medium text-gray-700">Poste occupé</label>
                <input type="text" id="position" name="position" value="{{ old('position') }}" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('position')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Monthly Salary -->
            <div class="mb-4">
                <label for="monthly_salary" class="block text-sm font-medium text-gray-700">Salaire mensuel (en HTG)</label>
                <input type="number" id="monthly_salary" name="monthly_salary" value="{{ old('monthly_salary') }}" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                @error('monthly_salary')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason for Pension Request -->
            <div class="mb-4">
                <label for="pension_reason" class="block text-sm font-medium text-gray-700">Raison de la demande de
                    pension</label>
                <textarea id="pension_reason" name="pension_reason" rows="4" required
                    class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">{{ old('pension_reason') }}</textarea>
                @error('pension_reason')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="mt-6">
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Soumettre la demande de pension
                </button>
            </div>
        </form>
    </div>
@endsection
