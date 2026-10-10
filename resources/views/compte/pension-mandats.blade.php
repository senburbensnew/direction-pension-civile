<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes mandats</h2>
    </x-slot>

    <div class="py-8 px-4 max-w-3xl mx-auto space-y-8">
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
        @endif

        <section class="bg-white border rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Demandes en cours et historique</h3>
            <ul class="divide-y">
                @forelse($mandats as $mandat)
                    <li class="py-3">
                        <p class="font-medium">{{ $mandat->beneficiaire?->displayName() ?? '—' }}</p>
                        <p class="text-sm text-gray-600">{{ $mandat->type }} · {{ $mandat->status?->label() }}</p>
                    </li>
                @empty
                    <li class="py-3 text-gray-500">Aucun mandat enregistré.</li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white border rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4">Nouvelle demande de mandat</h3>
            <form method="POST" action="{{ route('compte.mandats.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Prénom du pensionné</label>
                        <input type="text" name="beneficiaire_firstname" value="{{ old('beneficiaire_firstname') }}" class="w-full rounded-md border-gray-300" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom du pensionné</label>
                        <input type="text" name="beneficiaire_lastname" value="{{ old('beneficiaire_lastname') }}" class="w-full rounded-md border-gray-300" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">NIF du pensionné (optionnel)</label>
                    <input type="text" name="beneficiaire_nif" value="{{ old('beneficiaire_nif') }}" class="w-full rounded-md border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Code pension concerné</label>
                    <input type="text" name="code_pension" value="{{ old('code_pension') }}" class="w-full rounded-md border-gray-300" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Type de mandat</label>
                    <input type="text" name="type" value="{{ old('type', 'procuration_consulaire') }}" class="w-full rounded-md border-gray-300" required>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Date début</label>
                        <input type="date" name="date_debut" value="{{ old('date_debut') }}" class="w-full rounded-md border-gray-300">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Date fin</label>
                        <input type="date" name="date_fin" value="{{ old('date_fin') }}" class="w-full rounded-md border-gray-300">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Pièce justificative (PDF ou image)</label>
                    <input type="file" name="mandat_document" class="w-full text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full rounded-md border-gray-300">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Soumettre la demande</button>
            </form>
        </section>

        <p class="text-sm">
            <a href="{{ route('compte.affiliations.index') }}" class="text-indigo-700 hover:underline">← Retour aux codes pension</a>
        </p>
    </div>
</x-app-layout>
