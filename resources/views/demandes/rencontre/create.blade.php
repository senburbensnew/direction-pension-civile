@extends('layouts.main')

@section('title', 'Demande de rendez-vous')

@section('content')

<style>
    [x-cloak] {
        display: none !important;
    }

    .card-shadow {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .plateforme-option.is-active,
    .rdv-mode.is-active {
        border-color: #173052;
        background: #f4f6f9;
    }
</style>

@php

    $fieldClass = fn (string $field) =>
        'mt-1 block w-full py-2.5 px-4 border text-base focus:outline-none focus:ring-2 focus:ring-navy/20 focus:border-navy '
        . ($errors->has($field)
            ? 'border-red-400 bg-red-50'
            : 'border-gray-200');

    $oldModalite = old(
        'modalite',
        request('modalite', 'physique')
    );

    $detailsHasError = $errors->hasAny([
        'motif',
        'carte_pension',
        'lieu_rdv',
        'date_souhaitee',
        'heure_souhaitee'
    ]);

    $identiteHasError = $errors->hasAny([
        'prenom',
        'nom',
        'telephone'
    ]);

    $recapHasError = $errors->has(
        'confirmation_lu_accepte'
    );

@endphp

<div class="py-10 px-4 sm:px-6 lg:px-8">

    <div class="max-w-7xl mx-auto space-y-8">

        {{-- =====================================================
             TITRE
        ====================================================== --}}

        <div class="text-center">

            <span class="text-xs font-bold text-orange-500 uppercase tracking-widest">
                Communications
            </span>

            <h1 class="text-4xl font-bold text-navy mt-2 mb-3">
                Demande de rendez-vous
            </h1>

            <p class="text-gray-600 max-w-2xl mx-auto">
                Ministère de l’Économie et des Finances — Direction de la Pension Civile.
            </p>

        </div>


        {{-- =====================================================
             COMPTE PROVISIONNEL
        ====================================================== --}}

        @if(auth()->user()?->isProvisionnel())

            <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-lg px-4 py-3 text-sm">

                Compte provisoire — accès limité à la prise de rendez-vous jusqu’à la validation après le rendez-vous.

            </div>

        @endif


        {{-- =====================================================
             MESSAGE ERREUR SESSION
        ====================================================== --}}

        @if(session('error'))

            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">

                {{ session('error') }}

            </div>

        @endif


        {{-- =====================================================
             MESSAGE SUCCÈS
        ====================================================== --}}

        @if(session('success'))

            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">

                <div class="flex items-start gap-3 text-green-800">

                    <i class="fa-solid fa-check-circle text-green-500 mt-0.5 shrink-0"></i>

                    <p class="text-base">
                        {{ session('success') }}
                    </p>

                </div>

            </section>

            @include('demandes.rencontre._success-recap')

        @endif


        {{-- =====================================================
             ERREURS DE VALIDATION
        ====================================================== --}}

        @if($errors->any())

            <section class="bg-white border border-gray-200 card-shadow p-6 sm:p-8">

                <div class="flex items-start gap-3 text-red-800">

                    <i class="fa-solid fa-exclamation-circle text-red-500 mt-0.5 shrink-0"></i>

                    <ul class="text-base list-disc list-inside space-y-1">

                        @foreach($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            </section>

        @endif


        {{-- =====================================================
             MES RENDEZ-VOUS
        ====================================================== --}}

        @include('demandes.rencontre._mes-rendez-vous')


        @if($rdvActif ?? null)

            {{-- =================================================
                 RENDEZ-VOUS ACTIF
            ================================================== --}}

            <section class="bg-amber-50 border border-amber-200 card-shadow p-6 sm:p-8">

                <div class="flex items-start gap-3">

                    <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5 shrink-0"></i>

                    <div class="flex-1">

                        <p class="font-semibold text-navy">
                            Un rendez-vous est déjà en cours
                        </p>

                        <p class="text-sm text-gray-700 mt-1">

                            Vous ne pouvez pas en prendre un autre tant que le rendez-vous

                            <span class="font-semibold">
                                {{ $rdvActif->code }}
                            </span>

                            n’est pas annulé.

                        </p>


                        {{-- Détails du RDV actif --}}

                        <div class="mt-3 text-sm text-gray-700 space-y-1">

                            <p>

                                <span class="font-medium">
                                    Date :
                                </span>

                                {{ $rdvActif->data['date_souhaitee'] ?? '—' }}

                                à

                                {{ \App\Models\Demande::normalizeRencontreTime($rdvActif->data['heure_souhaitee'] ?? null) ?? '—' }}

                            </p>


                            @if(($rdvActif->data['modalite'] ?? '') === 'visio')

                                <p>

                                    <span class="font-medium">
                                        Mode :
                                    </span>

                                    Visioconférence

                                </p>

                            @elseif(!empty($rdvActif->data['lieu_rdv']))

                                <p>

                                    <span class="font-medium">
                                        Lieu :
                                    </span>

                                    {{ $rdvActif->data['lieu_rdv'] }}

                                </p>

                            @endif

                        </div>


                        {{-- Actions --}}

                        <div class="mt-4 flex flex-wrap items-center gap-3">

                            @if(!$rdvActif->rencontreStatut()->isTerminal())

                                <form
                                    action="{{ route('demandes.rencontre.annuler', $rdvActif) }}"
                                    method="POST"
                                    onsubmit="return confirm('Confirmez-vous l’annulation du rendez-vous {{ $rdvActif->code }} ?');"
                                    class="inline"
                                >

                                    @csrf

                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-lg hover:bg-red-700 transition"
                                    >

                                        <i class="fa-solid fa-xmark"></i>

                                        Annuler ce rendez-vous

                                    </button>

                                </form>

                            @endif

                        </div>

                    </div>

                </div>

            </section>

        @else

            {{-- =================================================
                 FORMULAIRE
            ================================================== --}}

            <form
                action="{{ route('demandes.rencontre.store') }}"
                method="POST"
                id="main-form"
                class="space-y-8"
                enctype="multipart/form-data"
                novalidate

                {{-- Feedback de soumission --}}
                @submit="submitting = true"
                x-on:pageshow.window="submitting = false"

                x-data="rdvBookingForm(@js([

                    'modalite' => $oldModalite,

                    'motif' => old('motif', ''),

                    'dateSouhaitee' => old('date_souhaitee', ''),

                    'heureSouhaitee' => old('heure_souhaitee', ''),

                    'lieuRdv' => old('lieu_rdv', ''),

                    'accepte' => (bool) old(
                        'confirmation_lu_accepte'
                    ),

                    'motifs' => $motifs ?? [],

                    'motifServices' => $motifServices ?? [],

                    'documents' => $documentsAPreparer ?? [],

                ]))"
            >

                @csrf


                {{-- =================================================
                     MODE DE RENDEZ-VOUS
                ================================================== --}}

                <details
                    class="dpc-accordion bg-white border border-gray-200 card-shadow"
                    open
                >

                    <summary>

                        <span>

                            <span class="block text-xl font-bold text-navy">
                                Choisir le mode de rendez-vous
                            </span>

                            <span class="block text-gray-600 font-normal mt-1 text-base">
                                Sélectionnez comment vous souhaitez rencontrer un agent de la DPC.
                            </span>

                        </span>

                    </summary>


                    <div class="dpc-accordion-body">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                            {{-- PRÉSENTIEL --}}

                            <label
                                class="rdv-mode flex items-start gap-4 border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-navy/40 transition"
                                :class="{ 'is-active': modalite === 'physique' }"
                            >

                                <input
                                    type="radio"
                                    name="modalite"
                                    value="physique"
                                    x-model="modalite"
                                    class="mt-1 accent-[#173052]"
                                >

                                <span>

                                    <span class="flex items-center gap-2 text-lg font-semibold text-navy">

                                        <i class="fa-solid fa-building"></i>

                                        Présentiel

                                    </span>

                                    <span class="block text-sm text-gray-600 mt-1">

                                        Rendez-vous dans un bureau de la DPC ou une Direction départementale.

                                    </span>

                                </span>

                            </label>


                            {{-- VISIO --}}

                            <label
                                class="rdv-mode flex items-start gap-4 border-2 border-gray-200 rounded-xl p-5 cursor-pointer hover:border-navy/40 transition"
                                :class="{ 'is-active': modalite === 'visio' }"
                            >

                                <input
                                    type="radio"
                                    name="modalite"
                                    value="visio"
                                    x-model="modalite"
                                    class="mt-1 accent-[#173052]"
                                >

                                <span>

                                    <span class="flex items-center gap-2 text-lg font-semibold text-navy">

                                        <i class="fa-solid fa-laptop"></i>

                                        Visioconférence

                                    </span>

                                    <span class="block text-sm text-gray-600 mt-1">

                                        Rendez-vous à distance avec un agent de la DPC.

                                    </span>

                                </span>

                            </label>

                        </div>


                        @error('modalite')

                            <p class="mt-2 text-base text-red-600">
                                {{ $message }}
                            </p>

                        @enderror

                    </div>

                </details>


                {{-- =================================================
                     DÉTAILS DU RENDEZ-VOUS
                ================================================== --}}

                <details
                    x-show="modalite"
                    x-cloak
                    class="dpc-accordion bg-white border border-gray-200 card-shadow"
                    @if($detailsHasError) open @endif
                    @toggle="if ($el.open) window.dispatchEvent(new Event('resize'))"
                >

                    <summary>

                        <span class="flex items-center gap-3">

                            <span class="text-2xl text-navy flex items-center justify-center shrink-0">

                                <i
                                    class="fa-solid fa-calendar-days"
                                    aria-hidden="true"
                                ></i>

                            </span>

                            <span class="block text-xl font-bold text-navy">
                                Détails du rendez-vous
                            </span>

                        </span>

                    </summary>


                    <div class="dpc-accordion-body">

                        {{-- MOTIF --}}

                        <div class="mb-5">

                            <label
                                for="motif"
                                class="block text-base font-medium text-gray-700"
                            >

                                Motif du rendez-vous

                                <span class="text-red-500">*</span>

                            </label>


                            <select
                                id="motif"
                                name="motif"
                                x-model="motif"
                                class="{{ $fieldClass('motif') }}"
                            >

                                <option value="">
                                    Préciser le motif
                                </option>

                                @foreach ($motifs ?? [] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('motif') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>


                            @error('motif')
                                <p class="mt-1 text-base text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p
                                x-show="motif"
                                x-cloak
                                class="mt-2 text-sm text-navy"
                            >

                                Service responsable :

                                <span
                                    class="font-semibold"
                                    x-text="serviceLabel()"
                                ></span>

                            </p>

                        </div>


                        {{-- MISE À JOUR --}}

                        <p
                            x-show="motif === 'mise_a_jour'"
                            x-cloak
                            class="mb-5 text-sm text-navy bg-slate-50 border border-navy/10 rounded-lg p-3"
                        >

                            Pour actualiser votre dossier, vous pouvez aussi remplir le

                            <a
                                href="{{ route('demandes.mise-a-jour.create') }}"
                                class="font-semibold underline"
                            >
                                questionnaire de mise à jour des informations
                            </a>.

                        </p>


                        {{-- CARTE DE PENSION --}}

                        <div class="mb-5">

                            <label
                                for="carte_pension"
                                class="block text-base font-medium text-gray-700"
                            >

                                Carte de pension (photo)

                                <span class="text-red-500">*</span>

                            </label>


                            <x-file-input
                                name="carte_pension"
                                accept="image/*,.pdf"
                                hint="Joignez une photo ou un scan de votre carte de pension (JPG, PNG ou PDF, 5 Mo max.)."
                            />

                        </div>


                        {{-- LIEU --}}

                        <div
                            x-show="modalite === 'physique'"
                            class="mb-5"
                        >

                            <label
                                for="lieu_rdv"
                                class="block text-base font-medium text-gray-700"
                            >

                                Lieu du rendez-vous

                                <span class="text-red-500">*</span>

                            </label>


                            <select
                                id="lieu_rdv"
                                name="lieu_rdv"
                                x-model="lieuRdv"
                                class="{{ $fieldClass('lieu_rdv') }}"
                            >

                                <option value="">
                                    Choisir un bureau
                                </option>

                                <option
                                    value="Siège de la DPC — Port-au-Prince"
                                    @selected(old('lieu_rdv') === 'Siège de la DPC — Port-au-Prince')
                                >
                                    Siège de la DPC — Port-au-Prince
                                </option>

                                @foreach ($lieux ?? [] as $lieu)

                                    <option
                                        value="{{ $lieu }}"
                                        @selected(old('lieu_rdv') === $lieu)
                                    >
                                        {{ $lieu }}
                                    </option>

                                @endforeach

                            </select>


                            @error('lieu_rdv')

                                <p class="mt-1 text-base text-red-600">
                                    {{ $message }}
                                </p>

                            @enderror

                        </div>


                        {{-- SCHEDULER --}}

                        @include('demandes.rencontre._scheduler')


                        {{-- VISIO --}}

                        <div
                            x-show="modalite === 'visio'"
                            class="mb-5 rounded-xl border border-navy/10 bg-slate-50 p-4"
                        >

                            <p class="text-base font-medium text-navy">
                                Lien de visioconférence
                            </p>

                            <p class="text-sm text-gray-600 mt-1">

                                Un lien sécurisé et unique sera généré automatiquement.
                                Seuls vous et l’agent désigné pourront y accéder.
                                Il devient actif 15 minutes avant l’heure du rendez-vous.

                            </p>

                        </div>

                    </div>

                </details>


                {{-- =================================================
                     IDENTITÉ
                ================================================== --}}

                {{-- @include('demandes.rencontre._identite') --}}


                {{-- =================================================
                     RÉCAPITULATIF
                ================================================== --}}

                @include('demandes.rencontre._recap')


                {{-- =================================================
                     CONFIRMATION
                ================================================== --}}

                <div
                    x-show="modalite"
                    x-cloak
                    class="flex justify-end"
                >

                    <button
                        type="submit"

                        {{-- Désactivation pendant l'envoi --}}
                        :disabled="!accepte || submitting"

                        class="inline-flex items-center justify-center gap-2 px-8 py-3 bg-navy text-white font-semibold hover:opacity-90 transition disabled:opacity-40 disabled:cursor-not-allowed min-w-[150px]"
                    >

                        {{-- État normal --}}

                        <template x-if="!submitting">

                            <span class="inline-flex items-center gap-2">

                                <i class="fa-solid fa-check"></i>

                                Confirmer

                            </span>

                        </template>


                        {{-- État loading --}}

                        <template x-if="submitting">

                            <span
                                class="inline-flex items-center gap-2"
                                aria-live="polite"
                            >

                                <i
                                    class="fa-solid fa-spinner fa-spin"
                                    aria-hidden="true"
                                ></i>

                                Enregistrement…

                            </span>

                        </template>

                    </button>

                </div>

            </form>

        @endif

    </div>

</div>


{{-- =============================================================
     ALPINE.JS
============================================================= --}}

<script>

    document.addEventListener('alpine:init', () => {

        Alpine.data('rdvBookingForm', (initial) => ({

            modalite: initial.modalite || '',

            motif: initial.motif || '',

            dateSouhaitee:
                initial.dateSouhaitee || '',

            heureSouhaitee:
                initial.heureSouhaitee || '',

            lieuRdv:
                initial.lieuRdv || '',

            accepte:
                Boolean(initial.accepte),

            /*
             * État de soumission.
             *
             * false = bouton normal
             * true  = formulaire en cours d'envoi
             */
            submitting: false,

            motifs:
                initial.motifs || {},

            motifServices:
                initial.motifServices || {},

            documents:
                initial.documents || {},


            motifLabel() {
                return this.motifs[this.motif] || '—';
            },


            serviceLabel() {
                return this.motifServices[
                    this.motif
                ] || '—';
            },

            recapDateHeure() {
                if (
                    !this.dateSouhaitee ||
                    !this.heureSouhaitee
                ) {

                    return 'Aucun créneau sélectionné';

                }

                return (
                    this.dateSouhaitee
                    + ' à '
                    + this.heureSouhaitee
                );
            },

            recapLieuOuLien() {

                if (
                    this.modalite === 'physique'
                ) {

                    return this.lieuRdv || '—';

                }

                return 'Lien sécurisé unique — actif 15 minutes avant le rendez-vous';

            },

            documentsPourModalite() {
                return this.documents[
                    this.modalite
                ] || [];
            },
        }));
    });
</script>

@endsection