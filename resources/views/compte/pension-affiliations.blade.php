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

        <div class="text-center">
            <p class="text-xs font-bold text-orange-500 uppercase tracking-widest">Compte pension</p>
            <h1 class="text-3xl sm:text-4xl font-bold text-navy mt-2 mb-2">Mes codes pension</h1>
            <p class="text-sm text-gray-500 max-w-2xl mx-auto">
                Gérez les codes rattachés à votre dossier ou à ceux des bénéficiaires auxquels vous avez accès.
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
                        <li class="py-4 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-lg font-semibold text-gray-900">{{ $affiliation->code_pension }}</span>
                                @if($affiliation->is_primary)
                                    <span class="text-xs font-semibold bg-blue-100 text-blue-900 px-2.5 py-0.5 rounded-full">Principal</span>
                                @endif
                                @if(isset($activeAffiliation) && $activeAffiliation?->id === $affiliation->id)
                                    <span class="text-xs text-gray-500">(sélectionné dans la bannière)</span>
                                @endif
                            </div>
                            @if($beneficiaire->affiliations->count() > 1)
                                <form method="POST" action="{{ route('compte.affiliations.destroy', $affiliation) }}" onsubmit="return confirm('Retirer ce code ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 text-sm font-medium hover:text-red-800 hover:underline">
                                        Retirer
                                    </button>
                                </form>
                            @endif
                        </li>
                    @empty
                        <li class="py-4 text-gray-500">Aucun code rattaché.</li>
                    @endforelse
                </ul>

                <form method="POST" action="{{ route('compte.affiliations.store') }}" class="mt-6 pt-6 border-t border-gray-100 grid sm:grid-cols-2 lg:grid-cols-3 gap-4 items-end">
                    @csrf
                    <input type="hidden" name="beneficiaire_id" value="{{ $beneficiaire->id }}">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <label for="code_pension_{{ $beneficiaire->id }}" class="block text-sm font-medium text-gray-700 mb-1">Ajouter un code</label>
                        <input
                            type="text"
                            id="code_pension_{{ $beneficiaire->id }}"
                            name="code_pension"
                            value="{{ old('beneficiaire_id') == $beneficiaire->id ? old('code_pension') : '' }}"
                            class="{{ $inputClass }}"
                            placeholder="8-12345 ou 7-AB123"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="flex flex-wrap items-center gap-4 sm:col-span-2 lg:col-span-2">
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                name="is_primary"
                                value="1"
                                class="rounded border-gray-300 text-navy focus:ring-navy"
                                @checked(old('beneficiaire_id') == $beneficiaire->id && old('is_primary'))
                            >
                            Définir comme principal
                        </label>
                        <button type="submit" class="{{ $btnClass }}">
                            Ajouter
                        </button>
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
