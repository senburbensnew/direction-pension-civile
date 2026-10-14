<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Demander une relation avec un bénéficiaire</h2>
    </x-slot>

    @php
        $inputClass = 'w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500';
    @endphp

    <x-portal-page class="max-w-xl">
        <form method="POST" action="{{ route('compte.relations.store') }}" enctype="multipart/form-data" class="bg-white border rounded-xl shadow-sm p-6 space-y-4">
            @csrf

            <div>
                <label for="code_pension" class="block text-sm font-medium text-gray-700 mb-1">Code pension du bénéficiaire</label>
                <input type="text" name="code_pension" id="code_pension" value="{{ old('code_pension') }}" class="{{ $inputClass }}" required autocomplete="off">
                @error('code_pension') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="type_relation" class="block text-sm font-medium text-gray-700 mb-1">Type de relation</label>
                <select name="type_relation" id="type_relation" class="{{ $inputClass }}" required>
                    @foreach($relationChoices as $choice)
                        <option value="{{ $choice->value }}" @selected(old('type_relation') === $choice->value)>{{ $choice->label() }}</option>
                    @endforeach
                </select>
                @error('type_relation') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="motif" class="block text-sm font-medium text-gray-700 mb-1">Motif (optionnel)</label>
                <textarea name="motif" id="motif" rows="3" class="{{ $inputClass }}">{{ old('motif') }}</textarea>
            </div>

            <div>
                <label for="justificatif" class="block text-sm font-medium text-gray-700 mb-1">Pièce justificative</label>
                <input type="file" name="justificatif" id="justificatif" class="{{ $inputClass }}" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('justificatif') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn btn-primary">Soumettre la demande</button>
                <a href="{{ route('compte.beneficiaires.index') }}" class="btn btn-ghost">Annuler</a>
            </div>
        </form>
    </x-portal-page>
</x-app-layout>
