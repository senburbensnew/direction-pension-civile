<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes mandats</h2>
    </x-slot>

@php
    $inputClass = 'w-full rounded-none border-gray-300 shadow-sm focus:border-navy focus:ring-navy';
    $btnClass = 'px-5 py-2.5 bg-navy hover:bg-orange-500 text-white text-sm font-semibold transition-colors';
    $statusBadge = fn ($status) => match ($status?->value ?? '') {
        'actif' => 'bg-green-100 text-green-900',
        'en_attente' => 'bg-amber-100 text-amber-900',
        'refuse' => 'bg-red-100 text-red-900',
        'revoque', 'expire' => 'bg-gray-100 text-gray-700',
        default => 'bg-gray-100 text-gray-700',
    };
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<x-portal-page bottom="pb-10" class="space-y-8">

        <div class="text-center">
            <p class="text-xs font-bold text-orange-500 uppercase tracking-widest">Compte pension</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-navy mt-2 mb-2">Mes mandats</h1>
            <p class="text-sm text-gray-500 max-w-2xl mx-auto">
                Demandez l’autorisation d’agir pour le compte d’un pensionné (procuration, mandat, etc.) et suivez vos demandes.
            </p>
        </div>

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <span class="text-2xl text-navy" aria-hidden="true">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Demandes en cours et historique</h2>
            </div>

            <ul class="divide-y divide-gray-100">
                @forelse($mandats as $mandat)
                    <li class="py-4 flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-semibold text-gray-900">
                                {{ $mandat->beneficiaire?->displayName() ?? '—' }}
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ str_replace('_', ' ', $mandat->type) }}
                                @if($mandat->beneficiaire?->primaryAffiliation()?->code_pension)
                                    · <span class="font-mono">{{ $mandat->beneficiaire->primaryAffiliation()->code_pension }}</span>
                                @endif
                            </p>
                            @if($mandat->date_debut || $mandat->date_fin)
                                <p class="text-xs text-gray-500 mt-1">
                                    @if($mandat->date_debut)
                                        Du {{ $mandat->date_debut->format('d/m/Y') }}
                                    @endif
                                    @if($mandat->date_fin)
                                        au {{ $mandat->date_fin->format('d/m/Y') }}
                                    @endif
                                </p>
                            @endif
                            @if($mandat->notes)
                                <p class="text-xs text-gray-500 mt-2 max-w-prose">{{ $mandat->notes }}</p>
                            @endif
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full shrink-0 {{ $statusBadge($mandat->status) }}">
                            {{ $mandat->status?->label() ?? '—' }}
                        </span>
                    </li>
                @empty
                    <li class="py-6 text-center text-gray-500">
                        Aucun mandat enregistré pour le moment.
                    </li>
                @endforelse
            </ul>
        </section>

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <span class="text-2xl text-navy" aria-hidden="true">
                    <i class="fa-solid fa-file-signature"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Nouvelle demande de mandat</h2>
            </div>

            <form method="POST" action="{{ route('compte.mandats.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="beneficiaire_firstname" class="block text-sm font-medium text-gray-700 mb-1">Prénom du pensionné</label>
                        <input type="text" id="beneficiaire_firstname" name="beneficiaire_firstname" value="{{ old('beneficiaire_firstname') }}" class="{{ $inputClass }}" required>
                    </div>
                    <div>
                        <label for="beneficiaire_lastname" class="block text-sm font-medium text-gray-700 mb-1">Nom du pensionné</label>
                        <input type="text" id="beneficiaire_lastname" name="beneficiaire_lastname" value="{{ old('beneficiaire_lastname') }}" class="{{ $inputClass }}" required>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="beneficiaire_nif" class="block text-sm font-medium text-gray-700 mb-1">NIF du pensionné (optionnel)</label>
                        <input type="text" id="beneficiaire_nif" name="beneficiaire_nif" value="{{ old('beneficiaire_nif') }}" class="{{ $inputClass }}" autocomplete="off">
                    </div>
                    <div>
                        <label for="code_pension" class="block text-sm font-medium text-gray-700 mb-1">Code pension concerné</label>
                        <input type="text" id="code_pension" name="code_pension" value="{{ old('code_pension') }}" class="{{ $inputClass }}" placeholder="8-12345 ou 7-AB123" required autocomplete="off">
                    </div>
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type de mandat</label>
                    <input type="text" id="type" name="type" value="{{ old('type', 'procuration_consulaire') }}" class="{{ $inputClass }}" required>
                    <p class="text-xs text-gray-500 mt-1">Ex. procuration consulaire, mandat spécial, tutelle, etc.</p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">Date début (optionnel)</label>
                        <input type="date" id="date_debut" name="date_debut" value="{{ old('date_debut') }}" class="{{ $inputClass }}">
                    </div>
                    <div>
                        <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">Date fin (optionnel)</label>
                        <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}" class="{{ $inputClass }}">
                    </div>
                </div>
                <div>
                    <label for="mandat_document" class="block text-sm font-medium text-gray-700 mb-1">Pièce justificative</label>
                    <input type="file" id="mandat_document" name="mandat_document" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-none file:border-0 file:bg-navy file:text-white file:font-semibold hover:file:bg-orange-500 file:transition-colors" required accept=".pdf,.jpg,.jpeg,.png">
                    <p class="text-xs text-gray-500 mt-1">PDF ou image · max. 5 Mo</p>
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optionnel)</label>
                    <textarea id="notes" name="notes" rows="3" class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                </div>
                <div class="pt-2">
                    <button type="submit" class="{{ $btnClass }}">
                        Soumettre la demande
                    </button>
                </div>
            </form>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600 pt-2">
            <a href="{{ route('compte.affiliations.index') }}" class="text-navy font-medium hover:underline">
                ← Mes codes pension
            </a>
            <a href="{{ route('profile.edit') }}" class="text-indigo-700 font-semibold hover:underline">
                Mon profil
            </a>
        </div>
</x-portal-page>
</x-app-layout>
