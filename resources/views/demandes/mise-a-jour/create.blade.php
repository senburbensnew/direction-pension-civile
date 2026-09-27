@extends('layouts.main')

@section('title', 'Mise à jour des informations du pensionné')

@section('content')
@php
    $input = fn (string $field) => 'mt-1 block w-full py-2.5 px-4 border text-base focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy '
        . ($errors->has($field) ? 'border-red-400 bg-red-50' : 'border-gray-200 rounded-lg');
@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto space-y-6">
        <div class="text-center">
            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">Pensionné</span>
            <h1 class="text-3xl font-bold text-navy mt-2 mb-2">Mise à jour des informations</h1>
            <p class="text-gray-600 text-sm">
                Permettre à la Direction de la Pension Civile de disposer d’informations fiables, complètes et actualisées.
            </p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('demandes.mise-a-jour.store') }}" enctype="multipart/form-data"
              class="space-y-6" x-data="{ changement: '{{ old('changement', '') }}', situation: '{{ old('situation_matrimoniale', '') }}' }">
            @csrf
            @php
                $openSection = fn (array $fields) => $errors->hasAny($fields) ? 'open' : '';
            @endphp

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" open>
                <summary>
                    <span class="text-lg font-bold text-navy">1. Identification du pensionné</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nom</label>
                        <input name="nom" value="{{ old('nom', $identite['nom']) }}" required class="{{ $input('nom') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Prénom(s)</label>
                        <input name="prenom" value="{{ old('prenom', $identite['prenom']) }}" required class="{{ $input('prenom') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Numéro de pension</label>
                        <input name="numero_pension" value="{{ old('numero_pension', $identite['numero_pension']) }}" required class="{{ $input('numero_pension') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Matricule</label>
                        <input name="matricule" value="{{ old('matricule') }}" class="{{ $input('matricule') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">NIF</label>
                        <input name="nif" value="{{ old('nif', $identite['nif']) }}" required placeholder="000-000-000-0" class="{{ $input('nif') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">CINU</label>
                        <input name="cinu" value="{{ old('cinu', $identite['cinu']) }}" class="{{ $input('cinu') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Date de naissance</label>
                        <input type="date" name="date_naissance" value="{{ old('date_naissance') }}" required class="{{ $input('date_naissance') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Lieu de naissance</label>
                        <input name="lieu_naissance" value="{{ old('lieu_naissance') }}" required class="{{ $input('lieu_naissance') }}">
                    </div>
                </div>
                </div>
            </details>

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" {{ $openSection(['adresse', 'departement_commune', 'telephone', 'telephone_secondaire', 'email']) }}>
                <summary>
                    <span class="text-lg font-bold text-navy">2. Coordonnées</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Adresse actuelle</label>
                    <input name="adresse" value="{{ old('adresse') }}" required class="{{ $input('adresse') }}">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Département / Commune</label>
                    <input name="departement_commune" value="{{ old('departement_commune') }}" required class="{{ $input('departement_commune') }}">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Téléphone principal</label>
                        <input name="telephone" value="{{ old('telephone', $identite['telephone']) }}" required placeholder="+509XXXXXXXX" class="{{ $input('telephone') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Téléphone secondaire</label>
                        <input name="telephone_secondaire" value="{{ old('telephone_secondaire') }}" placeholder="+509XXXXXXXX" class="{{ $input('telephone_secondaire') }}">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Adresse courriel</label>
                    <input type="email" name="email" value="{{ old('email', $identite['email']) }}" required class="{{ $input('email') }}">
                </div>
                </div>
            </details>

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" {{ $openSection(['situation_matrimoniale', 'conjoint_nom', 'conjoint_telephone']) }}>
                <summary>
                    <span class="text-lg font-bold text-navy">3. Situation familiale</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <div class="flex flex-wrap gap-3 text-sm">
                    @foreach($situationsMatrimoniales as $value => $label)
                        <label class="inline-flex items-center gap-2">
                            <input type="radio" name="situation_matrimoniale" value="{{ $value }}" x-model="situation"
                                   @checked(old('situation_matrimoniale') === $value) required>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="situation === 'marie' || situation === 'veuf'" x-cloak>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nom et prénom du conjoint(e)</label>
                        <input name="conjoint_nom" value="{{ old('conjoint_nom') }}" class="{{ $input('conjoint_nom') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Téléphone du conjoint(e)</label>
                        <input name="conjoint_telephone" value="{{ old('conjoint_telephone') }}" placeholder="+509XXXXXXXX" class="{{ $input('conjoint_telephone') }}">
                    </div>
                </div>
                </div>
            </details>

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" {{ $openSection(['numero_compte', 'institution_financiere', 'lieu_paiement', 'situation_pension']) }}>
                <summary>
                    <span class="text-lg font-bold text-navy">4. Informations relatives à la pension</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <div>
                    <label class="text-sm font-medium text-gray-700">Numéro de compte / mode de paiement</label>
                    <input name="numero_compte" value="{{ old('numero_compte') }}" required class="{{ $input('numero_compte') }}">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Institution financière</label>
                        <input name="institution_financiere" value="{{ old('institution_financiere') }}" required class="{{ $input('institution_financiere') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Commune / lieu de paiement</label>
                        <input name="lieu_paiement" value="{{ old('lieu_paiement') }}" required class="{{ $input('lieu_paiement') }}">
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <p class="font-medium text-gray-700">Situation actuelle</p>
                    @foreach($situationsPension as $value => $label)
                        <label class="flex items-center gap-2">
                            <input type="radio" name="situation_pension" value="{{ $value }}" @checked(old('situation_pension') === $value) required>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
                </div>
            </details>

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" {{ $openSection(['contact_nom', 'contact_lien', 'contact_telephone', 'contact_adresse']) }}>
                <summary>
                    <span class="text-lg font-bold text-navy">5. Personne à contacter en cas de besoin</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nom et prénom</label>
                        <input name="contact_nom" value="{{ old('contact_nom') }}" required class="{{ $input('contact_nom') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Lien avec le pensionné</label>
                        <input name="contact_lien" value="{{ old('contact_lien') }}" required class="{{ $input('contact_lien') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Téléphone</label>
                        <input name="contact_telephone" value="{{ old('contact_telephone') }}" required placeholder="+509XXXXXXXX" class="{{ $input('contact_telephone') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Adresse</label>
                        <input name="contact_adresse" value="{{ old('contact_adresse') }}" required class="{{ $input('contact_adresse') }}">
                    </div>
                </div>
                </div>
            </details>

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" {{ $openSection(['changement', 'changements', 'changement_autre']) }}>
                <summary>
                    <span class="text-lg font-bold text-navy">6. Mise à jour des informations</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <p class="text-sm text-gray-600">Y a-t-il eu un changement depuis votre dernière déclaration ?</p>
                <div class="flex gap-6 text-sm">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="changement" value="oui" x-model="changement" @checked(old('changement') === 'oui') required> Oui
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="changement" value="non" x-model="changement" @checked(old('changement') === 'non') required> Non
                    </label>
                </div>
                <div x-show="changement === 'oui'" x-cloak class="space-y-2 text-sm">
                    <p class="font-medium text-gray-700">Si oui, préciser :</p>
                    @foreach($changements as $value => $label)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="changements[]" value="{{ $value }}" @checked(in_array($value, old('changements', []), true))>
                            {{ $label }}
                        </label>
                    @endforeach
                    <input name="changement_autre" value="{{ old('changement_autre') }}" placeholder="Préciser (autre)"
                           class="{{ $input('changement_autre') }}">
                </div>
                </div>
            </details>

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" {{ $openSection(['piece_identite', 'acte_etat_civil', 'justificatif_domicile', 'document_bancaire', 'autre_justificatif']) }}>
                <summary>
                    <span class="text-lg font-bold text-navy">7. Pièces justificatives</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <p class="text-sm text-gray-500">Selon la nature de la modification. Formats acceptés : PDF, JPG, PNG (5 Mo max.).</p>
                <div>
                    <label class="text-sm font-medium text-gray-700">Copie d’une pièce d’identité</label>
                    <input type="file" name="piece_identite" accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Acte ou extrait d’acte d’état civil</label>
                    <input type="file" name="acte_etat_civil" accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Justificatif de domicile</label>
                    <input type="file" name="justificatif_domicile" accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Document bancaire</label>
                    <input type="file" name="document_bancaire" accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700">Autre document demandé par la DPC</label>
                    <input type="file" name="autre_justificatif" accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-1 block w-full text-sm">
                </div>
                </div>
            </details>

            <details class="dpc-accordion bg-white border border-gray-200 rounded-2xl" {{ $openSection(['declaration_nom', 'declaration_date', 'signature', 'certification']) }}>
                <summary>
                    <span class="text-lg font-bold text-navy">8. Déclaration du pensionné</span>
                </summary>
                <div class="dpc-accordion-body space-y-4">
                <p class="text-sm text-gray-600">
                    Je certifie que les informations fournies dans le présent formulaire sont exactes, complètes et à jour,
                    et m’engage à informer la Direction de la Pension Civile de toute modification ultérieure affectant ma situation.
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nom et prénom</label>
                        <input name="declaration_nom" value="{{ old('declaration_nom', $identite['declaration_nom']) }}" required class="{{ $input('declaration_nom') }}">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Date</label>
                        <input type="date" name="declaration_date" value="{{ old('declaration_date', now()->toDateString()) }}" required class="{{ $input('declaration_date') }}">
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 mb-1 block">Signature</label>
                    <x-signature-pad name="signature" />
                </div>
                <label class="flex items-start gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="certification" value="1" class="mt-1" @checked(old('certification')) required>
                    <span>Je certifie l’exactitude des informations fournies et autorise leur utilisation aux fins de gestion et de mise à jour de mon dossier de pension.</span>
                </label>
                </div>
            </details>

            <button type="submit" class="w-full py-3 bg-navy text-white font-semibold rounded-xl hover:opacity-90">
                Transmettre le questionnaire
            </button>
        </form>
    </div>
</div>
@endsection
