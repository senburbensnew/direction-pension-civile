<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Demander une relation</h2>
    </x-slot>

@php
    $inputClass = 'w-full rounded-none border-gray-300 shadow-sm focus:border-navy focus:ring-navy';
    $btnClass = 'px-5 py-2.5 bg-navy hover:bg-orange-500 text-white text-sm font-semibold transition-colors';
@endphp

<style>
    .card-shadow { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
</style>

<x-portal-page bottom="pb-10" class="w-full space-y-8">
        <x-compte-page-intro
            title="Demander une relation"
            description="Vous êtes père, mère ou tuteur d’un pensionné mineur ? Soumettez une demande pour accéder à son dossier après validation par un agent."
            :show-pension-context="false"
        />

        @if($errors->any())
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-lg border border-sky-100 bg-sky-50/70 px-4 py-3 text-left text-sm">
            <p class="font-semibold text-navy">Avant de commencer</p>
            <ul class="mt-2 list-disc list-inside space-y-1 text-xs text-gray-700 leading-relaxed">
                <li>Le code pension doit correspondre au dossier du mineur concerné.</li>
                <li>Joignez une pièce justificative de votre lien (acte de naissance, jugement de tutelle, etc.).</li>
                <li>Un agent des formalités examinera votre demande ; vous serez notifié une fois la relation activée.</li>
            </ul>
        </div>

        @if(auth()->user()?->canAccessFullServices())
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-left text-sm">
                <p class="text-xs text-gray-700 leading-relaxed">
                    Ce formulaire s’adresse aux personnes qui ont <span class="font-semibold">déjà un compte actif</span> sur le portail.
                    La demande de création de compte (profil mineur) est réservée aux personnes qui n’ont pas encore d’accès au système — elle ne doit pas être utilisée pour ouvrir un second compte.
                </p>
            </div>
        @elseif(auth()->user()?->isProvisionnel())
            <div class="rounded-lg border border-amber-100 bg-amber-50/80 px-4 py-3 text-left text-sm">
                <p class="font-semibold text-navy">Compte provisoire</p>
                <p class="mt-2 text-xs text-gray-700 leading-relaxed">
                    Si vous avez ouvert votre accès via une demande de création de compte (profil mineur), votre lien de représentation est déjà rattaché à ce dossier.
                    Suivez son avancement dans « Mes bénéficiaires » et finalisez le rendez-vous aux formalités — ne soumettez pas une nouvelle demande de compte.
                </p>
            </div>
        @endif

        <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                <span class="text-2xl text-navy" aria-hidden="true">
                    <i class="fa-solid fa-user-group"></i>
                </span>
                <h2 class="text-xl font-bold text-navy">Formulaire de demande</h2>
            </div>

            <form method="POST" action="{{ route('compte.relations.store') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <x-pension-code-input
                    :value="old('code_pension')"
                    label="Code pension du bénéficiaire"
                    :input-class="$inputClass.' py-2.5 text-sm'"
                />

                <div>
                    <label for="type_relation" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Type de relation <span class="text-red-500">*</span>
                    </label>
                    <select name="type_relation" id="type_relation" class="{{ $inputClass }} py-2.5 text-sm bg-white" required>
                        @foreach($relationChoices as $choice)
                            <option value="{{ $choice->value }}" @selected(old('type_relation') === $choice->value)>{{ $choice->label() }}</option>
                        @endforeach
                    </select>
                    @error('type_relation')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="motif" class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">
                        Motif <span class="font-normal normal-case text-gray-400">(optionnel)</span>
                    </label>
                    <textarea
                        name="motif"
                        id="motif"
                        rows="3"
                        placeholder="Précisez le contexte si nécessaire…"
                        class="{{ $inputClass }} py-2.5 text-sm"
                    >{{ old('motif') }}</textarea>
                </div>

                <x-file-input
                    name="justificatif"
                    id="justificatif"
                    accept=".pdf,.jpg,.jpeg,.png"
                    required
                    label="Pièce justificative"
                    hint="PDF ou image · max. 5 Mo"
                />

                <div class="flex flex-col-reverse sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3 pt-2 border-t border-gray-100">
                    <a
                        href="{{ route('compte.beneficiaires.index') }}"
                        class="inline-flex items-center justify-center px-5 py-2.5 border border-navy text-navy hover:bg-navy hover:text-white text-sm font-semibold transition-colors text-center"
                    >
                        Annuler
                    </a>
                    <button type="submit" class="{{ $btnClass }} inline-flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane text-xs" aria-hidden="true"></i>
                        Soumettre la demande
                    </button>
                </div>
            </form>
        </section>

        <div class="flex flex-wrap items-center justify-between gap-4 text-sm text-gray-600 pt-2">
            <a href="{{ route('compte.beneficiaires.index') }}" class="text-navy font-medium hover:underline">
                ← Mes bénéficiaires
            </a>
            <a href="{{ route('compte.mandats.index') }}" class="text-navy font-medium hover:underline">
                Demander un mandat →
            </a>
        </div>
</x-portal-page>
</x-app-layout>
