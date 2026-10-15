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

        <x-compte-page-intro
            title="Mes mandats"
            description="Demandez l’autorisation d’agir pour le compte d’un pensionné (procuration, mandat, etc.) et suivez vos demandes."
        />

        @if(isset($demandesCompteMandataire) && $demandesCompteMandataire->isNotEmpty())
            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">
                <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                    <span class="text-2xl text-navy" aria-hidden="true"><i class="fa-solid fa-user-plus"></i></span>
                    <h2 class="text-xl font-bold text-navy">Demande de compte mandataire en cours</h2>
                </div>
                <ul class="divide-y divide-gray-100">
                    @foreach($demandesCompteMandataire as $dcc)
                        <li class="py-4">
                            <p class="font-semibold text-gray-900">Dossier {{ $dcc->code }}</p>
                            @if($dcc->pension_code)
                                <p class="text-sm text-gray-600 mt-1 font-mono">{{ $dcc->pension_code }}</p>
                            @endif
                            <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                                Votre mandat est enregistré avec ce dossier. Finalisez le rendez-vous aux formalités ; après acceptation du compte, un agent devra encore approuver le mandat pour activer l’accès au dossier du pensionné.
                            </p>
                        </li>
                    @endforeach
                </ul>
            </section>
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
                                {{ $mandat->typeLabel() }}
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
                            @if($mandat->isFromDemandeCreationCompte() && $mandat->demandeCreationCompte)
                                <p class="text-xs text-sky-800 mt-2">
                                    Via demande de compte <span class="font-mono">{{ $mandat->demandeCreationCompte->code }}</span>
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

        @if(auth()->user()?->canAccessFullServices())
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-left text-sm">
                <p class="text-xs text-gray-700 leading-relaxed">
                    Ce formulaire s’adresse aux personnes qui ont <span class="font-semibold">déjà un compte actif</span>.
                    La demande de création de compte (profil mandataire) est réservée à celles qui n’ont pas encore d’accès au portail — pas pour créer un second compte.
                </p>
            </div>
        @elseif(auth()->user()?->isProvisionnel())
            <div class="rounded-lg border border-amber-100 bg-amber-50/80 px-4 py-3 text-left text-sm">
                <p class="font-semibold text-navy">Compte provisoire</p>
                <p class="mt-2 text-xs text-gray-700 leading-relaxed">
                    Si vous êtes mandataire via une demande de création de compte en cours, le mandat est déjà enregistré avec ce dossier.
                    Consultez la section ci-dessus et finalisez le rendez-vous aux formalités — ne soumettez pas une nouvelle demande de compte.
                </p>
            </div>
        @endif

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
                        <x-pension-code-input
                            :value="old('code_pension')"
                            label="Code pension concerné"
                            :input-class="$inputClass"
                        />
                    </div>
                </div>
                @php
                    $selectedType = old('type_mandat', \App\Enums\PensionMandatTypeEnum::PROCURATION_CONSULAIRE->value);
                @endphp
                <div
                    class="space-y-3"
                    x-data="{ typeMandat: @js($selectedType) }"
                >
                    <div>
                        <label for="type_mandat" class="block text-sm font-medium text-gray-700 mb-1">
                            Type de mandat <span class="text-red-500">*</span>
                        </label>
                        <select
                            name="type_mandat"
                            id="type_mandat"
                            class="{{ $inputClass }} bg-white"
                            required
                            x-model="typeMandat"
                        >
                            @foreach($mandatTypeChoices as $choice)
                                <option value="{{ $choice->value }}" @selected($selectedType === $choice->value)>
                                    {{ $choice->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('type_mandat')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div x-show="typeMandat === 'autre'" x-cloak>
                        <label for="type_autre_precision" class="block text-sm font-medium text-gray-700 mb-1">
                            Précisez le type de mandat <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="type_autre_precision"
                            name="type_autre_precision"
                            value="{{ old('type_autre_precision') }}"
                            class="{{ $inputClass }}"
                            maxlength="120"
                            placeholder="Ex. procuration bancaire, mandat ad hoc…"
                            :required="typeMandat === 'autre'"
                        >
                        @error('type_autre_precision')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="date_debut" class="block text-sm font-medium text-gray-700 mb-1">
                            Date de début <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date_debut" name="date_debut" value="{{ old('date_debut') }}" class="{{ $inputClass }}" required>
                        @error('date_debut')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="date_fin" class="block text-sm font-medium text-gray-700 mb-1">
                            Date de fin <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}" class="{{ $inputClass }}" required>
                        @error('date_fin')
                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <x-file-input
                    name="mandat_document"
                    id="mandat_document"
                    accept=".pdf,.jpg,.jpeg,.png"
                    required
                    label="Pièce justificative"
                    hint="PDF ou image · max. 5 Mo"
                />
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
            <a href="{{ route('compte.beneficiaires.index') }}" class="text-navy font-medium hover:underline">
                ← Mes bénéficiaires
            </a>
            <a href="{{ route('compte.affiliations.index') }}" class="text-navy font-medium hover:underline">
                Mes codes pension
            </a>
            <a href="{{ route('profile.edit') }}" class="text-indigo-700 font-semibold hover:underline">
                Mon profil
            </a>
        </div>
</x-portal-page>
</x-app-layout>
