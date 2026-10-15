<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mes codes pension</h2>
    </x-slot>

@php
    $inputClass = 'w-full rounded-none border-gray-300 shadow-sm focus:border-navy focus:ring-navy';
    $btnClass = 'px-5 py-2.5 bg-navy hover:bg-orange-500 text-white text-sm font-semibold transition-colors';
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<x-portal-page bottom="pb-10" class="space-y-8">

        <x-compte-page-intro
            title="Mes codes pension"
            description="Gérez les codes rattachés à votre dossier ou à ceux des bénéficiaires auxquels vous avez accès."
        />

        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('info'))
            <div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sky-900 text-sm">
                {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @forelse($beneficiaires as $beneficiaire)
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-gray-100 pb-4 mb-4">
                    <div>
                        <h2 class="text-xl font-bold text-navy">
                            {{ $beneficiaire->displayName() }}
                        </h2>
                        @if($beneficiaire->is_mineur)
                            <p class="text-sm text-amber-800 mt-1">Pensionné mineur</p>
                        @endif
                    </div>
                    @if(isset($activeBeneficiaire) && $activeBeneficiaire?->id === $beneficiaire->id)
                        <span class="text-xs font-semibold uppercase tracking-wide bg-sky-100 text-sky-900 px-2.5 py-1 rounded-full">
                            Contexte actif
                        </span>
                    @endif
                </div>

                <ul class="divide-y divide-gray-100">
                    @forelse($beneficiaire->affiliations as $affiliation)
                        @php
                            $editingDescriptionDefault = $errors->has('loc_description')
                                && (string) old('_affiliation_id') === (string) $affiliation->id;
                        @endphp
                        <li
                            class="py-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-start sm:justify-between"
                            x-data="{ editingDescription: @json($editingDescriptionDefault) }"
                        >
                            <div class="min-w-0 flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono text-lg font-semibold text-gray-900">{{ $affiliation->code_pension }}</span>
                                    @if($affiliation->is_primary)
                                        <span class="text-xs font-semibold bg-blue-100 text-blue-900 px-2.5 py-0.5 rounded-full">Principal</span>
                                    @endif
                                    @if(isset($activeAffiliation) && $activeAffiliation?->id === $affiliation->id)
                                        <span class="text-xs text-gray-500">(sélectionné dans la bannière)</span>
                                    @endif
                                </div>

                                <div x-show="! editingDescription" x-cloak>
                                    @if(filled($affiliation->loc_description))
                                        <p class="text-sm text-gray-700 max-w-xl">
                                            <span class="text-xs font-medium uppercase tracking-wide text-gray-500">Description ·</span>
                                            {{ $affiliation->loc_description }}
                                        </p>
                                    @else
                                        <p class="text-sm text-gray-400 italic max-w-xl">Description non renseignée</p>
                                    @endif
                                </div>

                                <form
                                    x-show="editingDescription"
                                    x-cloak
                                    method="POST"
                                    action="{{ route('compte.affiliations.update-description', $affiliation) }}"
                                    class="flex flex-col sm:flex-row sm:items-end gap-2 max-w-xl"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="_affiliation_id" value="{{ $affiliation->id }}">
                                    <div class="flex-1 min-w-0">
                                        <label for="loc_description_{{ $affiliation->id }}" class="block text-xs font-medium text-gray-500 mb-1">
                                            Description <span class="text-red-500">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="loc_description_{{ $affiliation->id }}"
                                            name="loc_description"
                                            value="{{ $editingDescriptionDefault ? old('loc_description') : $affiliation->loc_description }}"
                                            maxlength="255"
                                            required
                                            placeholder="Ex. retraite principale, second emploi…"
                                            class="{{ $inputClass }} py-2 text-sm"
                                        >
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0 sm:pb-0.5">
                                        <button type="submit" class="{{ $btnClass }} px-4 py-2 text-sm">
                                            Enregistrer
                                        </button>
                                        <button
                                            type="button"
                                            class="px-3 py-2 text-sm font-medium text-gray-600 hover:text-navy"
                                            @click="editingDescription = false"
                                        >
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                            <div class="flex flex-wrap items-center gap-1 shrink-0">
                                <button
                                    type="button"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-md text-navy hover:bg-sky-50 hover:text-orange-600 transition-colors"
                                    title="Modifier la description"
                                    @click="editingDescription = ! editingDescription"
                                    :aria-expanded="editingDescription"
                                >
                                    <i class="fas fa-pencil-alt text-sm" aria-hidden="true"></i>
                                    <span class="sr-only">Modifier la description</span>
                                </button>
                                @if(! $affiliation->is_primary)
                                    <form method="POST" action="{{ route('compte.affiliations.set-primary', $affiliation) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button
                                            type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md text-amber-700 hover:bg-amber-50 hover:text-orange-600 transition-colors"
                                            title="Définir comme principal"
                                        >
                                            <i class="fas fa-star text-sm" aria-hidden="true"></i>
                                            <span class="sr-only">Définir comme principal</span>
                                        </button>
                                    </form>
                                @endif
                                @if($beneficiaire->affiliations->count() > 1 && ! $affiliation->is_primary)
                                    <form method="POST" action="{{ route('compte.affiliations.destroy', $affiliation) }}" onsubmit="return confirm('Retirer ce code ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            type="submit"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-md text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors"
                                            title="Retirer ce code"
                                        >
                                            <i class="fas fa-trash-alt text-sm" aria-hidden="true"></i>
                                            <span class="sr-only">Retirer ce code</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-4 text-gray-500">Aucun code rattaché.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('compte.affiliations.store') }}" class="mt-8 pt-6 border-t border-gray-200">
                    @csrf
                    <input type="hidden" name="beneficiaire_id" value="{{ $beneficiaire->id }}">

                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-gradient-to-b from-slate-50/90 to-white shadow-sm">
                        <div class="flex gap-3 border-b border-gray-200 bg-white px-4 py-3.5 sm:px-5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-navy/10 text-navy" aria-hidden="true">
                                <i class="fas fa-plus text-sm"></i>
                            </span>
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-navy sm:text-base">
                                    Ajouter un code pension
                                </h3>
                                <p class="mt-0.5 text-xs text-gray-500 leading-relaxed">
                                    Libellé obligatoire pour identifier ce code dans votre dossier et la bannière de contexte.
                                </p>
                            </div>
                        </div>

                        <div class="space-y-5 p-4 sm:p-5">
                            <div>
                                <label for="loc_description_new_{{ $beneficiaire->id }}" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                                    Description <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="loc_description_new_{{ $beneficiaire->id }}"
                                    name="loc_description"
                                    value="{{ old('beneficiaire_id') == $beneficiaire->id ? old('loc_description') : '' }}"
                                    maxlength="255"
                                    required
                                    placeholder="Ex. retraite principale, second emploi…"
                                    class="{{ $inputClass }} bg-white py-2.5 text-sm"
                                >
                            </div>

                            <div class="max-w-md space-y-4">
                                <x-pension-code-input
                                    id="code_pension_{{ $beneficiaire->id }}"
                                    :value="old('beneficiaire_id') == $beneficiaire->id ? old('code_pension') : ''"
                                    label="Code pension"
                                    :show-hint="true"
                                    :input-class="$inputClass.' bg-white py-2.5 text-sm'"
                                />

                                <label class="flex cursor-pointer items-center gap-2 text-left text-sm text-gray-700">
                                    <input
                                        type="checkbox"
                                        name="is_primary"
                                        value="1"
                                        class="shrink-0 rounded border-gray-300 text-navy focus:ring-navy"
                                        @checked(old('beneficiaire_id') == $beneficiaire->id && old('is_primary'))
                                    >
                                    <i class="fas fa-star shrink-0 text-xs text-amber-600" aria-hidden="true"></i>
                                    <span class="shrink-0 font-medium text-navy">Code principal</span>
                                    <span class="text-xs text-gray-500">
                                        — utilisé par défaut dans la bannière et les demandes
                                    </span>
                                </label>

                                <button type="submit" class="{{ $btnClass }} inline-flex items-center justify-center gap-2 px-6 py-2.5">
                                    <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                                    Ajouter le code
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>
        @empty
            <section class="bg-white border border-gray-200 card-shadow p-8 text-center text-gray-600">
                <p class="font-medium text-navy">Aucun bénéficiaire accessible</p>
                <p class="text-sm mt-2 max-w-md mx-auto">
                    Votre compte n’a pas encore de dossier pension rattaché. Contactez la DPC ou complétez votre demande de compte.
                </p>
                <a href="{{ route('profile.edit') }}" class="inline-block mt-4 text-sm font-semibold text-indigo-700 hover:underline">
                    Retour au profil
                </a>
            </section>
        @endforelse

        <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600 pt-2">
            <a href="{{ route('profile.edit') }}" class="text-navy font-medium hover:underline">
                ← Mon profil
            </a>
            <p>
                <a href="{{ route('compte.mandats.index') }}" class="text-indigo-700 font-semibold hover:underline">
                    Demander un mandat
                </a>
                pour agir au nom d’un autre pensionné.
            </p>
        </div>
</x-portal-page>
</x-app-layout>
