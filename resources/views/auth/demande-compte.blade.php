<x-guest-layout :wide="true">
    @php
        $isMandataire = (bool) old('is_mandataire');
        $isMineur = (bool) old('is_mineur') && ! $isMandataire;
        $representantEstPensionne = (bool) old('representant_est_pensionne');
        $mandataireEstPensionne = (bool) old('mandataire_est_pensionne');
        $alsoTitulaire = ($isMineur && $representantEstPensionne)
            || ($isMandataire && $mandataireEstPensionne);
        $userType = ($isMineur || $isMandataire) && ! $alsoTitulaire
            ? 'externe'
            : 'pensionne';
        $accountProfile = match (true) {
            $isMandataire => 'mandataire',
            $isMineur => 'mineur',
            default => 'titulaire',
        };

        $types = [
            'pensionne' => [
                'label' => __('messages.pensioner'),
                'icon' => 'fa-user-clock',
            ],
        ];

        $pieceTypes = __('guest.account_request.piece_types');
        $lienTypes = __('guest.account_request.lien_types');

        $serverErrors = collect($errors->messages())
            ->map(fn (array $messages) => $messages[0] ?? '')
            ->all();

        /*
         * Champs par étape — sert au routage des erreurs serveur.
         */
        $step1Fields = [
            'is_mineur',
            'is_mandataire',
            'mandat_designation',
            'piece_identite_type',
            'piece_identite',
            'acte_naissance',
            'representant_lien',
            'piece_identite_representant_type',
            'piece_identite_representant',
        ];

        $step2Fields = [
            'ocr_documents_json',
        ];

        $step3Fields = [
            'user_type',
            'telephone',
            'nif',
            'pension_code',
            'password',
            'adresse',
        ];

        $step4Fields = [
            'accept_terms',
        ];

        $errorKeys = collect($errors->keys());

        $hasStep1Errors = $errorKeys->intersect($step1Fields)->isNotEmpty();
        $hasStep2Errors = $errorKeys->intersect($step2Fields)->isNotEmpty();
        $hasStep3Errors = $errorKeys->intersect($step3Fields)->isNotEmpty();
        $hasStep4Errors = $errorKeys->intersect($step4Fields)->isNotEmpty();

        /*
         * Priorité : on remonte à l'étape la plus précoce en erreur.
         */
        $initialStep = match (true) {
            $hasStep1Errors => 1,
            $hasStep2Errors => 2,
            $hasStep3Errors => 3,
            $hasStep4Errors => 4,
            default         => 1,
        };

        $stashedFiles = app(\App\Services\Form\FormUploadStashService::class)
            ->displayNamesForRequest(request());

        $ocrDocumentsInitial = [];
        if ($rawOcr = old('ocr_documents_json')) {
            $decodedOcr = json_decode($rawOcr, true);
            if (is_array($decodedOcr)) {
                $ocrDocumentsInitial = $decodedOcr;
            }
        }

        $wizardSteps = __('guest.account_request_js.steps');
        $initialStepMeta = collect($wizardSteps)->firstWhere('id', $initialStep) ?? ($wizardSteps[0] ?? []);
        $initialStepNumber = max(1, (int) (collect($wizardSteps)->search(
            fn (array $step) => (int) ($step['id'] ?? 0) === $initialStep
        ) + 1));

        $formState = [
            'step' => $initialStep,
            'isMineur' => $isMineur,
            'isMandataire' => $isMandataire,
            'accountProfile' => $accountProfile,
            'userType' => $userType,
            'stashedFiles' => $stashedFiles,
            'ocrDocuments' => $ocrDocumentsInitial,

            'telephone' => old('telephone', ''),
            'nif' => old('nif', ''),
            'pensionCode' => old('pension_code', ''),
            'adresse' => old('adresse', ''),

            'pieceType' => old('piece_identite_type', ''),
            'representantLien' => old('representant_lien', ''),
            'representantPieceType' => old('piece_identite_representant_type', ''),
            'representantEstPensionne' => $representantEstPensionne,
            'mandataireEstPensionne' => $mandataireEstPensionne,
            'acceptTerms' => (bool) old('accept_terms'),

            'ocrUrl' => route('demandes.compte.ocr'),
            'availabilityUrl' => route('demandes.compte.disponibilite'),
            'rdvFallbackUrl' => route('demandes.rencontre.create', absolute: true),

            'fieldErrors' => $serverErrors,

            'ui' => __('guest.account_request'),
            'i18n' => __('guest.account_request_js'),
        ];
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
        }

        /* ============================================================
         * SCROLL ANCHOR POUR LES ERREURS
         * ============================================================ */

        [name="nif"],
        [name="pension_code"],
        [name="telephone"],
        [name="adresse"],
        [name="password"],
        [name="accept_terms"],
        [name="piece_identite"],
        [name="acte_naissance"],
        [name="piece_identite_representant"],
        [name="piece_identite_type"],
        [name="piece_identite_representant_type"],
        [name="representant_lien"] {
            scroll-margin-top: 6rem;
        }

        /* ============================================================
         * STEPS
         * ============================================================ */

        .compte-steps {
            display: flex;
            align-items: flex-start;
            margin: 0 0 1.5rem;
            padding: 0;
            list-style: none;
        }

        .compte-step {
            flex: 1;
            position: relative;
            min-width: 0;
            text-align: center;
        }

        .compte-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 1.125rem;
            left: calc(50% + 1.15rem);
            right: calc(-50% + 1.15rem);
            height: 2px;
            background: #e5e7eb;
            z-index: 0;
        }

        .compte-step.is-complete:not(:last-child)::after {
            background: #173052;
        }

        .compte-step-btn {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            background: none;
            border: 0;
            padding: 0;
            cursor: pointer;
        }

        .compte-step-btn:disabled {
            cursor: default;
        }

        .compte-step-dot {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 9999px;
            border: 2px solid #d1d5db;
            background: #fff;
            color: #9ca3af;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            transition:
                background-color .2s ease,
                border-color .2s ease,
                color .2s ease,
                box-shadow .2s ease;
        }

        .compte-step.is-complete .compte-step-dot,
        .compte-step.is-current .compte-step-dot {
            background: #173052;
            border-color: #173052;
            color: #fff;
        }

        .compte-step.is-current .compte-step-dot {
            box-shadow: 0 0 0 4px rgba(23, 48, 82, 0.12);
        }

        .compte-step-label {
            margin-top: 0.6rem;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #9ca3af;
            line-height: 1.2;
        }

        .compte-step.is-current .compte-step-label {
            color: #173052;
        }

        .compte-step.is-complete .compte-step-label {
            color: #374151;
        }

        .compte-step-caption {
            display: none;
            margin-top: 0.2rem;
            font-size: 0.7rem;
            color: #9ca3af;
            font-weight: 400;
            letter-spacing: 0;
            text-transform: none;
        }

        @media (max-width: 639px) {
            .compte-step-dot {
                width: 1.85rem;
                height: 1.85rem;
                font-size: 0.68rem;
            }

            .compte-step:not(:last-child)::after {
                top: 0.9rem;
                left: calc(50% + 1rem);
                right: calc(-50% + 1rem);
            }

            .compte-step-label {
                font-size: 0.62rem;
                letter-spacing: 0.03em;
            }
        }

        /* ============================================================
         * DOCUMENT CARDS
         * ============================================================ */

        .compte-document-card {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background: #fff;
            padding: 1rem;
        }

        .compte-document-card.required {
            border-color: #d1d5db;
        }

        /* ============================================================
         * OCR
         * ============================================================ */

        .compte-ocr-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        @media (min-width: 768px) {
            .compte-ocr-layout {
                grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
                align-items: start;
            }
        }

        .compte-ocr-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 16rem;
            max-height: 32rem;
            overflow: auto;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
        }

        .compte-ocr-preview img {
            display: block;
            width: 100%;
            max-height: 32rem;
            object-fit: contain;
        }

        /* ============================================================
         * COLLAPSIBLES
         * ============================================================ */

        .compte-collapsible {
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            background: #fff;
            overflow: hidden;
        }

        .compte-collapsible + .compte-collapsible {
            margin-top: 0.75rem;
        }

        .compte-collapsible-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            width: 100%;
            padding: 0.85rem 1rem;
            background: none;
            border: 0;
            text-align: left;
            cursor: pointer;
            font-size: 0.875rem;
            font-weight: 600;
            color: #1f2937;
            transition: background-color .15s ease;
        }

        .compte-collapsible-header:hover {
            background: #f9fafb;
        }

        .compte-collapsible-title {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            min-width: 0;
        }

        .compte-collapsible-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 9999px;
            background: #f3f4f6;
            color: #173052;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .compte-collapsible-sub {
            display: block;
            font-size: 0.7rem;
            font-weight: 400;
            color: #6b7280;
            margin-top: 0.1rem;
        }

        .compte-collapsible-chevron {
            color: #9ca3af;
            font-size: 0.75rem;
            transition: transform .2s ease;
            flex-shrink: 0;
        }

        .compte-collapsible-chevron.is-open {
            transform: rotate(180deg);
        }

        .compte-collapsible-body {
            padding: 0 1rem 1rem;
            border-top: 1px solid #f3f4f6;
            padding-top: 0.85rem;
        }
    </style>

    <div
        data-dpc-demande-compte-form
        x-data="demandeCompteForm(@js($formState))"
    >

        {{-- ============================================================
             EN-TÊTE
             ============================================================ --}}

        <div class="mb-4 text-center">
            <h2 class="text-xl font-bold text-gray-800">
                {{ __('guest.account_request.title') }}
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                {{ __('guest.account_request.step_label') }}
                <span x-text="currentStepNumber">{{ $initialStepNumber }}</span>
                /
                <span x-text="totalSteps">{{ count($wizardSteps) }}</span>

                <span class="text-gray-300 mx-1">·</span>

                <span x-text="currentStepCaption">{{ $initialStepMeta['caption'] ?? '' }}</span>
            </p>
        </div>

        {{-- ============================================================
             RÉSUMÉ GLOBAL DES ERREURS (visible à toutes les étapes)
             ============================================================ --}}

        <div
            x-show="errorCount > 0"
            x-cloak
            x-transition.opacity
            class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4"
            role="alert"
            aria-live="polite"
            x-ref="errorSummary"
        >
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-triangle text-red-600 mt-0.5"></i>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-red-800">
                        <span x-text="errorCount"></span>
                        <span x-text="errorCount > 1 ? ui.errors_plural : ui.errors_singular"></span>
                        <span x-text="' ' + ui.errors_before_submit"></span>
                    </p>

                    <ul class="mt-2 space-y-1 text-sm text-red-700">
                        <template x-for="err in errorSummary" :key="err.field">
                            <li>
                                <button
                                    type="button"
                                    @click="jumpToError(err.field)"
                                    class="text-left hover:underline focus:outline-none focus:underline"
                                >
                                    <i class="fas fa-arrow-right text-[10px] mr-1 opacity-60"></i>
                                    <span class="font-medium" x-text="err.label + ' : '"></span>
                                    <span x-text="err.message"></span>
                                    <span
                                        class="ml-1 text-[11px] text-red-500 italic"
                                        x-text="ui.error_goto_step + ' ' + err.step"
                                    ></span>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>

                <button
                    type="button"
                    @click="clearAllErrors()"
                    class="text-red-400 hover:text-red-600 shrink-0"
                    :aria-label="ui.hide_error_summary"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- ============================================================
             INDICATEUR DES ÉTAPES
             ============================================================ --}}

        <ol class="compte-steps" aria-label="{{ __('guest.account_request.steps_aria') }}">
            <template x-for="(item, index) in visibleSteps" :key="item.id">
                <li
                    class="compte-step"
                    :class="{ 'is-complete': item.id < step, 'is-current': item.id === step }"
                >
                    <button
                        type="button"
                        class="compte-step-btn"
                        @click="goTo(item.id)"
                        :disabled="item.id > step"
                        :aria-current="item.id === step ? 'step' : null"
                    >
                        <span class="compte-step-dot">
                            <i
                                x-show="item.id < step"
                                class="fas fa-check text-[11px]"
                            ></i>

                            <span
                                x-show="item.id >= step"
                                x-text="index + 1"
                            ></span>
                        </span>

                        <span
                            class="compte-step-label"
                            x-text="item.label"
                        ></span>

                        <span
                            class="compte-step-caption"
                            x-text="item.caption"
                        ></span>
                    </button>
                </li>
            </template>
        </ol>

        {{-- ============================================================
             FORMULAIRE PRINCIPAL
             ============================================================ --}}

        <form
            method="POST"
            action="{{ route('demandes.compte.store') }}"
            class="space-y-4"
            enctype="multipart/form-data"
            novalidate
            x-ref="form"
            @submit.prevent="onSubmit"
            x-on:pageshow.window="handlePageShow($event)"
        >
            @csrf

            {{-- Payload OCR --}}
            <input
                type="hidden"
                name="ocr_documents_json"
                :value="JSON.stringify(ocrDocuments)"
            >
            {{-- Idempotency key --}}
            <input
                type="hidden"
                name="idempotency_key"
                :value="idempotencyKey"
            >

            {{-- ========================================================
                 ÉTAPE 1
                 TÉLÉVERSEMENT DES DOCUMENTS
                 ======================================================== --}}

            <div
                x-show="step === 1"
                class="space-y-5"
            >
                <!-- <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">
                            Téléversement des documents
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Sélectionnez les documents nécessaires à la création du compte.
                            Les informations seront ensuite lues automatiquement par OCR.
                        </p>
                    </div>
                </div> -->

                {{-- Profil du demandeur --}}
                <fieldset class="space-y-2">
                    <legend class="text-sm font-medium text-gray-700 mb-1">
                        {{ __('guest.account_request.profile_legend') }}
                    </legend>
                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:gap-x-6 sm:gap-y-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input
                                type="radio"
                                class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-400"
                                value="titulaire"
                                x-model="accountProfile"
                                @change="applyAccountProfile()"
                            >
                            <span>{{ __('guest.account_request.profile_titulaire') }}</span>
                        </label>
                        <!-- <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input
                                type="radio"
                                class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-400"
                                value="mineur"
                                x-model="accountProfile"
                                @change="applyAccountProfile()"
                            >
                            <span>{{ __('guest.account_request.profile_mineur') }}</span>
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700">
                            <input
                                type="radio"
                                class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-400"
                                value="mandataire"
                                x-model="accountProfile"
                                @change="applyAccountProfile()"
                            >
                            <span>{{ __('guest.account_request.profile_mandataire') }}</span>
                        </label> -->
                    </div>
                    <p class="text-xs text-gray-500">
                        <span x-show="accountProfile === 'titulaire'" x-text="ui.profile_titulaire_hint"></span>
                        <span x-show="accountProfile === 'mineur'" x-cloak x-text="ui.profile_mineur_hint"></span>
                        <span x-show="accountProfile === 'mandataire'" x-cloak x-text="ui.profile_mandataire_hint"></span>
                        <span x-show="isMineur || isMandataire" x-cloak role="status">
                            {{ __('guest.account_request.proxy_existing_account_notice') }}
                            <a
                                href="{{ route('login') }}"
                                class="font-medium text-gray-600 underline underline-offset-2 hover:text-gray-800"
                            >
                                {{ __('guest.account_request.proxy_existing_account_login') }}
                            </a>
                        </span>
                    </p>
                    <input type="hidden" name="is_mineur" id="is_mineur" :value="isMineur ? '1' : '0'">
                    <input type="hidden" name="is_mandataire" id="is_mandataire" :value="isMandataire ? '1' : '0'">
                    <input type="hidden" name="user_type" :value="userType">
                </fieldset>

                {{-- ====================================================
                     MAJEUR / MANDATAIRE
                     ==================================================== --}}

                <div
                    x-show="!isMineur"
                    x-cloak
                    class="compte-document-card required space-y-4"
                >
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800" x-text="isMandataire ? ui.doc_mandataire_title : ui.doc_pensionne_title"></h4>
                        <p class="text-xs text-gray-500 mt-1">
                            <span x-show="!isMandataire" x-text="ui.doc_upload_hint"></span>
                            <span x-show="isMandataire" x-cloak x-text="ui.doc_mandataire_account"></span>
                        </p>
                    </div>

                    <div>
                        <label
                            for="piece_identite_type"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('guest.account_request.document_type') }}
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="piece_identite_type"
                            name="piece_identite_type"
                            x-model="pieceType"
                            @change="clearFieldError('piece_identite_type')"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('piece_identite_type')"
                        >
                            <option value="">{{ __('guest.account_request.choose_document') }}</option>

                            @foreach ($pieceTypes as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(old('piece_identite_type') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        <p
                            x-cloak
                            x-show="fieldErrors.piece_identite_type"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.piece_identite_type"
                        ></p>
                    </div>

                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">
                            {{ __('guest.account_request.document') }}
                            <span class="text-red-500">*</span>
                        </span>

                        <x-file-input
                            name="piece_identite"
                            id="piece_identite"
                            accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                            hint="{{ __('guest.account_request.file_hint_image_pdf') }}"
                            on-change="files.piece_identite = $event.target.files[0]?.name || ''; clearFieldError('piece_identite')"
                            x-bind:class="fieldErrors.piece_identite ? 'text-red-600' : ''"
                        />

                        <p
                            x-cloak
                            x-show="fieldErrors.piece_identite"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.piece_identite"
                        ></p>
                    </div>

                    <div
                        x-show="isMandataire"
                        x-cloak
                        class="border-t border-gray-100 pt-4"
                    >
                        <label class="inline-flex items-start gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="mandataire_est_pensionne"
                                value="1"
                                class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-400"
                                x-model="mandataireEstPensionne"
                                @change="syncUserTypeFromProfile()"
                            >
                            <span class="text-sm text-gray-700">
                                {{ __('guest.account_request.mandataire_pensionne') }}
                            </span>
                        </label>
                    </div>

                    <div x-show="isMandataire" x-cloak class="space-y-2 border-t border-gray-100 pt-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">{{ __('guest.account_request.mandat_title') }}</h4>
                            <p class="text-xs text-gray-500 mt-1">{{ __('guest.account_request.mandat_hint') }}</p>
                        </div>
                        <x-file-input
                            name="mandat_designation"
                            id="mandat_designation"
                            accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                            hint="{{ __('guest.account_request.file_hint_pdf_image') }}"
                            on-change="files.mandat_designation = $event.target.files[0]?.name || ''; clearFieldError('mandat_designation')"
                            x-bind:class="fieldErrors.mandat_designation ? 'text-red-600' : ''"
                        />
                        <p x-cloak x-show="fieldErrors.mandat_designation" class="text-xs text-red-600" x-text="fieldErrors.mandat_designation"></p>
                    </div>

                </div>

                {{-- ====================================================
                     MINEUR
                     ==================================================== --}}
                <div
                    x-show="isMineur"
                    x-cloak
                    class="space-y-4"
                >
                    {{-- Acte de naissance --}}
                    <div class="compte-document-card required space-y-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">
                                {{ __('guest.account_request.birth_act_title') }}
                            </h4>

                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('guest.account_request.birth_act_hint') }}
                            </p>
                        </div>

                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-1">
                                Document
                                <span class="text-red-500">*</span>
                            </span>

                            <x-file-input
                                name="acte_naissance"
                                id="acte_naissance"
                                accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                                hint="JPG, PNG, WEBP ou PDF — 5 Mo max."
                                on-change="files.acte_naissance = $event.target.files[0]?.name || ''; clearFieldError('acte_naissance')"
                                x-bind:class="fieldErrors.acte_naissance ? 'text-red-600' : ''"
                            />

                            <p
                                x-cloak
                                x-show="fieldErrors.acte_naissance"
                                class="text-xs text-red-600 mt-1"
                                x-text="fieldErrors.acte_naissance"
                            ></p>
                        </div>
                    </div>

                    {{-- Représentant --}}
                    <div class="compte-document-card required space-y-4">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-800">
                                {{ __('guest.account_request.representant_id_title') }}
                            </h4>

                            <p class="text-xs text-gray-500 mt-1">
                                {{ __('guest.account_request.representant_id_hint') }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="representant_lien"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                {{ __('guest.account_request.legal_representative') }}
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="representant_lien"
                                name="representant_lien"
                                x-model="representantLien"
                                @change="clearFieldError('representant_lien')"
                                class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                                :class="borderClass('representant_lien')"
                            >
                                <option value="">{{ __('guest.account_request.choose') }}</option>

                                @foreach ($lienTypes as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('representant_lien') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <p
                                x-cloak
                                x-show="fieldErrors.representant_lien"
                                class="text-xs text-red-600 mt-1"
                                x-text="fieldErrors.representant_lien"
                            ></p>
                        </div>

                        <div>
                            <label
                                for="piece_identite_representant_type"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                {{ __('guest.account_request.id_piece_type') }}
                                <span class="text-red-500">*</span>
                            </label>

                            <select
                                id="piece_identite_representant_type"
                                name="piece_identite_representant_type"
                                x-model="representantPieceType"
                                @change="clearFieldError('piece_identite_representant_type')"
                                class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                                :class="borderClass('piece_identite_representant_type')"
                            >
                                <option value="">{{ __('guest.account_request.choose_document') }}</option>

                                @foreach ($pieceTypes as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('piece_identite_representant_type') === $value)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <p
                                x-cloak
                                x-show="fieldErrors.piece_identite_representant_type"
                                class="text-xs text-red-600 mt-1"
                                x-text="fieldErrors.piece_identite_representant_type"
                            ></p>
                        </div>

                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-1">
                                Document
                                <span class="text-red-500">*</span>
                            </span>

                            <x-file-input
                                name="piece_identite_representant"
                                id="piece_identite_representant"
                                accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                                hint="JPG, PNG, WEBP ou PDF — 5 Mo max."
                                on-change="files.piece_identite_representant = $event.target.files[0]?.name || ''; clearFieldError('piece_identite_representant')"
                                x-bind:class="fieldErrors.piece_identite_representant ? 'text-red-600' : ''"
                            />

                            <p
                                x-cloak
                                x-show="fieldErrors.piece_identite_representant"
                                class="text-xs text-red-600 mt-1"
                                x-text="fieldErrors.piece_identite_representant"
                            ></p>
                        </div>

                        <div class="border-t border-gray-100 pt-4">
                            <label class="inline-flex items-start gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    name="representant_est_pensionne"
                                    value="1"
                                    class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-400"
                                    x-model="representantEstPensionne"
                                    @change="syncUserTypeFromProfile()"
                                >
                                <span class="text-sm text-gray-700">
                                    {{ __('guest.account_request.representant_pensionne') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- <div class="rounded-lg bg-blue-50 border border-blue-100 p-3">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-[#173052] mt-0.5"></i>

                        <p class="text-xs text-gray-600">
                            Après avoir sélectionné les documents, cliquez sur
                            <strong>Suivant</strong>.
                            Le portail procédera automatiquement à leur lecture OCR.
                        </p>
                    </div>
                </div> -->
            </div>

            {{-- ============================================================
                 ÉTAPE 2
                 OCR
                 ============================================================ --}}

            <div
                x-show="step === 2"
                x-cloak
                class="space-y-5"
            >
                <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">
                            {{ __('guest.account_request.ocr_step_title') }}
                        </h3>

                        <p class="text-xs mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                            <span class="text-gray-500">
                                {{ __('guest.account_request.ocr_step_intro') }}
                            </span>

                            <span
                                class="inline-flex items-center gap-1.5 rounded-md border border-amber-400 bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-950 shadow-sm"
                                role="alert"
                            >
                                <i class="fas fa-exclamation-triangle text-amber-600 text-[10px]" aria-hidden="true"></i>
                                {{ __('guest.account_request.ocr_verify_warning') }}
                            </span>
                        </p>
                    </div>
                </div>

                <template x-if="ocrDocuments.length === 0">
                    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>

                            <div>
                                <p class="text-sm font-semibold text-yellow-800">
                                    {{ __('guest.account_request.ocr_no_result') }}
                                </p>

                                <p class="text-xs text-yellow-700 mt-1">
                                    {{ __('guest.account_request.ocr_no_result_hint') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </template>

                <template
                    x-for="doc in ocrDocuments"
                    :key="doc.key"
                >
                    <div class="rounded-xl border border-gray-200 p-4 space-y-3">

                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p
                                    class="text-sm font-semibold text-gray-800"
                                    x-text="doc.label"
                                ></p>

                                <p
                                    x-show="doc.error"
                                    class="text-xs text-red-600 mt-1"
                                >
                                    {{ __('guest.account_request.ocr_read_failed') }}
                                </p>
                            </div>

                            <span
                                x-show="doc.fields?.type_document"
                                class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700"
                                x-text="pieceTypeName(doc.fields.type_document)"
                            ></span>
                        </div>

                        <div class="compte-ocr-layout">

                            {{-- Document --}}
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">
                                    {{ __('guest.account_request.document') }}
                                </p>

                                <div class="compte-ocr-preview">

                                    <img
                                        x-show="previews[doc.key]?.url && !previews[doc.key]?.loading"
                                        :src="previews[doc.key]?.url"
                                        :alt="doc.label"
                                    >

                                    <p
                                        x-show="previews[doc.key]?.loading"
                                        class="text-sm text-gray-500 px-4 text-center"
                                    >
                                        <i class="fas fa-spinner fa-spin mr-1"></i>
                                        {{ __('guest.account_request.ocr_preview_loading') }}
                                    </p>

                                    <p
                                        x-show="previews[doc.key]?.error"
                                        class="text-sm text-amber-700 px-4 text-center"
                                    >
                                        {{ __('guest.account_request.ocr_preview_pdf_unavail') }}
                                    </p>

                                    <p
                                        x-show="!previews[doc.key]"
                                        class="text-sm text-gray-400 px-4 text-center"
                                    >
                                        {{ __('guest.account_request.ocr_preview_unavail') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Données OCR --}}
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">
                                    {{ __('guest.account_request.ocr_extracted_info') }}
                                </p>

                                <div class="space-y-2.5">

                                    <template
                                        x-for="field in fieldsFor(doc)"
                                        :key="doc.key + '-' + field.key"
                                    >
                                        <div>
                                            <label
                                                class="block text-xs font-medium text-gray-600 mb-1"
                                                :for="doc.key + '_' + field.key"
                                                x-text="field.label"
                                            ></label>

                                            <input
                                                type="text"
                                                :id="doc.key + '_' + field.key"
                                                x-model="doc.fields[field.key]"
                                                class="w-full py-2 px-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            >
                                        </div>
                                    </template>

                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="rounded-lg bg-gray-50 border border-gray-200 p-3">
                    <p class="text-xs text-gray-600">
                        <i class="fas fa-info-circle text-[#173052] mr-1"></i>
                        {{ __('guest.account_request.ocr_recap_hint') }}
                    </p>
                </div>
            </div>

            {{-- ============================================================
                 ÉTAPE 3
                 INFORMATIONS DU COMPTE
                 ============================================================ --}}

            <div
                x-show="step === 3"
                x-cloak
                class="space-y-5"
            >
                <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">
                            {{ __('guest.account_request.account_step_title') }}
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ __('guest.account_request.account_step_intro') }}
                        </p>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4">

                    {{-- Type de compte --}}
                    <div class="hidden">
                        <p class="text-sm font-medium text-gray-700 mb-2">
                            Type de compte
                            <span class="text-red-500">*</span>
                        </p>

                        <div class="flex flex-col gap-2">
                            @foreach ($types as $value => $meta)
                                @php
                                    $checked = $userType === $value;
                                @endphp

                                <label
                                    for="user_type_{{ $value }}"
                                    class="flex items-center gap-2 cursor-pointer border-2 rounded-lg px-3 py-2 transition-all select-none"
                                    :class="userType === '{{ $value }}' ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                >
                                    <input
                                        type="radio"
                                        id="user_type_{{ $value }}"
                                        value="{{ $value }}"
                                        class="sr-only"
                                        x-model="userType"
                                        @change="clearFieldError('user_type')"
                                    >

                                    <i class="fas {{ $meta['icon'] }} text-sm w-4 text-center"></i>

                                    <span class="text-sm font-medium">
                                        {{ $meta['label'] }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <p
                            x-cloak
                            x-show="fieldErrors.user_type"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.user_type"
                        ></p>
                    </div>

                    {{-- Téléphone --}}
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label
                                for="telephone"
                                class="block text-sm font-medium text-gray-700"
                            >
                                {{ __('guest.account_request.phone_number') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <x-format-hint-icon
                                :text="__('messages.format_hint_telephone')"
                                hint-id="telephone-format-hint"
                            />
                        </div>

                        <input
                            id="telephone"
                            type="tel"
                            name="telephone"
                            x-model="telephone"
                            @input="normalizeTelephone(); clearFieldError('telephone')"
                            placeholder="+50938123456"
                            inputmode="tel"
                            autocomplete="tel"
                            maxlength="16"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('telephone')"
                            aria-describedby="telephone-error"
                        >

                        <p
                            id="telephone-error"
                            x-cloak
                            x-show="fieldErrors.telephone"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.telephone"
                        ></p>
                    </div>

                    {{-- NIF --}}
                    <div>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label
                                for="nif"
                                class="block text-sm font-medium text-gray-700"
                            >
                                {{ __('guest.account_request.nif_label') }}
                                <span class="text-red-500">*</span>
                            </label>
                            <x-format-hint-icon
                                :text="__('messages.format_hint_nif')"
                                hint-id="nif-format-hint"
                            />
                        </div>

                        <input
                            id="nif"
                            type="text"
                            name="nif"
                            x-model="nif"
                            @input="
                                nif = nif.replace(/\D+/g, '').slice(0, 10)
                                        .replace(/^(\d{3})(\d{0,3})(\d{0,3})(\d?)$/,
                                                (_, a, b, c, d) => [a, b, c, d].filter(Boolean).join('-'));
                                clearFieldError('nif');
                                scheduleAvailabilityCheck('nif');
                            "
                            placeholder="000-000-000-0"
                            inputmode="numeric"
                            autocomplete="off"
                            maxlength="13"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('nif')"
                        >
                        <p
                            x-show="!fieldErrors.nif"
                            class="text-xs mt-1"
                            :class="nifStatus === 'error' ? 'text-red-600' : (nifStatus === 'ok' ? 'text-green-700' : 'text-gray-400')"
                            x-text="nifMessage"
                        ></p>

                        <p
                            x-cloak
                            x-show="fieldErrors.nif"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.nif"
                        ></p>
                    </div>

                    {{-- Code pension (optionnel) --}}
                    <div x-show="userType === 'pensionne' || isMineur || isMandataire" x-cloak>
                        <div class="flex items-center gap-1.5 mb-1">
                            <label
                                for="pension_code"
                                class="block text-sm font-medium text-gray-700"
                            >
                                <span
                                    x-show="!isMineur && !isMandataire"
                                    x-text="ui.pension_code_label"
                                ></span>
                                <span
                                    x-show="isMineur || isMandataire"
                                    x-cloak
                                    x-text="ui.pension_code_label_pensionne"
                                ></span>
                                <span class="text-xs font-normal text-gray-400">{{ __('guest.account_request.pension_code_optional') }}</span>
                            </label>
                            <x-format-hint-icon
                                :text="__('messages.format_hint_pension_code')"
                                hint-id="pension-code-format-hint"
                            />
                        </div>

                        <input
                            id="pension_code"
                            type="text"
                            name="pension_code"
                            x-model="pension_code"
                            @input="
                                pension_code = pension_code.toUpperCase();
                                clearFieldError('pension_code');
                                scheduleAvailabilityCheck('pension_code');
                            "
                            placeholder="{{ __('guest.account_request.pension_code_placeholder') }}"
                            inputmode="text"
                            autocomplete="off"
                            maxlength="7"
                            spellcheck="false"
                            autocapitalize="characters"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('pension_code')"
                        >

                        <p
                            x-show="!fieldErrors.pension_code"
                            class="text-xs text-gray-500 mt-1"
                            x-text="ui.pension_code_hint_optional"
                        ></p>

                        <p
                            x-show="!fieldErrors.pension_code"
                            class="text-xs mt-1"
                            :class="pension_codeStatus === 'error' ? 'text-red-600' : (pension_codeStatus === 'ok' ? 'text-green-700' : 'text-gray-400')"
                            x-text="pension_codeMessage"
                        ></p>

                        <p
                            x-cloak
                            x-show="fieldErrors.pension_code"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.pension_code"
                        ></p>
                    </div>

                    <p class="text-xs text-gray-500">
                        {!! __('guest.account_request.profile_later_hint', ['profile' => '<span class="font-medium">'.e(__('guest.account_request.my_profile')).'</span>']) !!}
                    </p>

                    {{-- Mot de passe --}}
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('messages.password') }}
                            <span class="text-red-500">*</span>
                        </label>

                        <div class="relative">
                            <input
                                id="password"
                                :type="showPassword ? 'text' : 'password'"
                                name="password"
                                autocomplete="new-password"
                                @input="clearFieldError('password')"
                                class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                                :class="borderClass('password')"
                                style="padding-right: 2.25rem;"
                                placeholder="••••••••"
                            >

                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                                :aria-label="showPassword
                                    ? @js(__('guest.login.hide_password'))
                                    : @js(__('guest.login.show_password'))"
                            >
                                <i
                                    :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"
                                    class="text-sm"
                                ></i>
                            </button>
                        </div>

                        <p
                            x-show="!fieldErrors.password"
                            class="text-xs text-gray-400 mt-1"
                        >
                            {{ __('guest.account_request.password_hint') }}
                        </p>

                        <p
                            x-cloak
                            x-show="fieldErrors.password"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.password"
                        ></p>
                    </div>

                    {{-- Adresse actuelle --}}
                    <div>
                        <label
                            for="adresse"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            {{ __('guest.account_request.current_address') }}
                            <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            id="adresse"
                            name="adresse"
                            rows="2"
                            x-model="adresse"
                            @input="clearFieldError('adresse')"
                            placeholder="{{ __('guest.account_request.address_placeholder') }}"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2 resize-none"
                            :class="borderClass('adresse')"
                        ></textarea>

                        <p
                            x-cloak
                            x-show="fieldErrors.adresse"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.adresse"
                        ></p>
                    </div>
                </div>
            </div>

            {{-- ============================================================
                 ÉTAPE 4
                 RÉCAPITULATIF ET VALIDATION
                 ============================================================ --}}

            <div
                x-show="step === 4"
                x-cloak
                class="space-y-5"
            >
                <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">
                            {{ __('guest.account_request.recap_step_title') }}
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ __('guest.account_request.recap_step_intro') }}
                        </p>
                    </div>
                </div>

                {{-- ========================================================
                     RÉCAPITULATIF DU COMPTE (dépliable)
                     ======================================================== --}}

                <div class="compte-collapsible">
                    <button
                        type="button"
                        class="compte-collapsible-header"
                        @click="openSections.resume = !openSections.resume"
                        :aria-expanded="openSections.resume"
                    >
                        <span class="compte-collapsible-title">
                            <span class="compte-collapsible-icon">
                                <i class="fas fa-user"></i>
                            </span>

                            <span>
                                {{ __('guest.account_request.recap_account') }}
                                <span class="compte-collapsible-sub">
                                    {{ __('guest.account_request.recap_account_sub') }}
                                </span>
                            </span>
                        </span>

                        <i
                            class="fas fa-chevron-down compte-collapsible-chevron"
                            :class="openSections.resume ? 'is-open' : ''"
                        ></i>
                    </button>

                    <div
                        x-show="openSections.resume"
                        x-cloak
                        class="compte-collapsible-body"
                    >
                        <div class="text-sm text-gray-700 space-y-2">

                            <p>
                                <span class="text-gray-500" x-text="ui.recap_account_type"></span>
                                <span x-text="userTypeLabel"></span>
                            </p>

                            <p>
                                <span class="text-gray-500" x-text="ui.recap_phone"></span>
                                <span x-text="telephone || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500" x-text="ui.recap_nif"></span>
                                <span x-text="nif || '—'"></span>
                            </p>

                            <p x-show="userType === 'pensionne' || isMineur || isMandataire" x-cloak>
                                <span class="text-gray-500" x-text="ui.recap_pension_code"></span>
                                <span x-text="pension_code || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500" x-text="ui.recap_password"></span>
                                {{ __('guest.account_request.recap_password_set') }}
                            </p>

                            <p>
                                <span class="text-gray-500" x-text="ui.recap_address"></span>
                                <span x-text="adresse || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500" x-text="ui.recap_minor"></span>
                                <span x-text="isMineur ? ui.yes : ui.no"></span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- ========================================================
                     PIÈCES JUSTIFICATIVES (dépliable)
                     ======================================================== --}}

                <div class="compte-collapsible">
                    <button
                        type="button"
                        class="compte-collapsible-header"
                        @click="openSections.pieces = !openSections.pieces"
                        :aria-expanded="openSections.pieces"
                    >
                        <span class="compte-collapsible-title">
                            <span class="compte-collapsible-icon">
                                <i class="fas fa-paperclip"></i>
                            </span>

                            <span>
                                {{ __('guest.account_request.recap_attachments') }}
                                <span class="compte-collapsible-sub">
                                    {{ __('guest.account_request.recap_attachments_sub') }}
                                </span>
                            </span>
                        </span>

                        <i
                            class="fas fa-chevron-down compte-collapsible-chevron"
                            :class="openSections.pieces ? 'is-open' : ''"
                        ></i>
                    </button>

                    <div
                        x-show="openSections.pieces"
                        x-cloak
                        class="compte-collapsible-body"
                    >
                        <div class="text-sm text-gray-700 space-y-2">

                            <template x-if="!isMineur">
                                <div class="space-y-1">
                                    <p>
                                        <span class="text-gray-500" x-text="ui.recap_document"></span>

                                        <span x-text="pieceTypeLabel"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500" x-text="ui.recap_file"></span>

                                        <span x-text="files.piece_identite || ui.to_attach"></span>
                                    </p>
                                </div>
                            </template>

                            <template x-if="isMineur">
                                <div class="space-y-1">
                                    <p>
                                        <span class="text-gray-500" x-text="ui.recap_birth_act"></span>

                                        <span x-text="files.acte_naissance || ui.to_attach"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500" x-text="ui.recap_representant"></span>

                                        <span x-text="representantLienLabel"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500" x-text="ui.recap_representant_id"></span>

                                        <span x-text="representantPieceTypeLabel"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500" x-text="ui.recap_file"></span>

                                        <span x-text="files.piece_identite_representant || ui.to_attach"></span>
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- ========================================================
                     DONNÉES OCR (dépliable)
                     ======================================================== --}}

                <div
                    class="compte-collapsible"
                    x-show="ocrDocuments.length > 0"
                    x-cloak
                >
                    <button
                        type="button"
                        class="compte-collapsible-header"
                        @click="openSections.ocr = !openSections.ocr"
                        :aria-expanded="openSections.ocr"
                    >
                        <span class="compte-collapsible-title">
                            <span class="compte-collapsible-icon">
                                <i class="fas fa-magic"></i>
                            </span>

                            <span>
                                {{ __('guest.account_request.recap_ocr') }}
                                <span class="compte-collapsible-sub">
                                    {{ __('guest.account_request.recap_ocr_sub') }}
                                </span>
                            </span>
                        </span>

                        <i
                            class="fas fa-chevron-down compte-collapsible-chevron"
                            :class="openSections.ocr ? 'is-open' : ''"
                        ></i>
                    </button>

                    <div
                        x-show="openSections.ocr"
                        x-cloak
                        class="compte-collapsible-body space-y-3"
                    >
                        <template
                            x-for="doc in ocrDocuments"
                            :key="'recap-' + doc.key"
                        >
                            <div class="rounded-lg border border-gray-100 bg-gray-50 p-3 space-y-2">

                                <div class="flex items-center justify-between gap-2">
                                    <p
                                        class="text-xs font-semibold text-gray-700"
                                        x-text="doc.label"
                                    ></p>

                                    <span
                                        x-show="doc.fields?.type_document"
                                        class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-700 uppercase tracking-wide"
                                        x-text="pieceTypeName(doc.fields.type_document)"
                                    ></span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-1 text-xs">

                                    <template
                                        x-for="field in fieldsFor(doc)"
                                        :key="'recap-' + doc.key + '-' + field.key"
                                    >
                                        <div
                                            x-show="(doc.fields[field.key] || '').toString().trim() !== ''"
                                            class="flex items-start justify-between gap-2 border-b border-dashed border-gray-200 py-1"
                                        >
                                            <span
                                                class="text-gray-500 shrink-0"
                                                x-text="field.label + ' :'"
                                            ></span>

                                            <span
                                                class="text-gray-800 font-medium text-right break-words"
                                                x-text="doc.fields[field.key]"
                                            ></span>
                                        </div>
                                    </template>

                                </div>

                                <p
                                    x-show="doc.error"
                                    class="text-[11px] text-red-600 italic"
                                >
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    {{ __('guest.account_request.ocr_recap_failed') }}
                                </p>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- ========================================================
                     ACCEPTATION DES CONDITIONS
                     ======================================================== --}}

                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <label class="flex items-start gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            name="accept_terms"
                            value="1"
                            x-model="acceptTerms"
                            @change="clearFieldError('accept_terms')"
                            {{ old('accept_terms') ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 rounded border-gray-300 text-navy focus:ring-navy"
                        >

                        <span class="text-sm text-gray-700">
                            {{ __('guest.account_request.accept_terms_prefix') }}

                            <a
                                href="{{ route('terms.policy') }}"
                                target="_blank"
                                class="text-navy hover:text-orange-500 font-medium underline"
                            >
                                {{ __('guest.account_request.terms_of_use') }}
                            </a>

                            {{ __('guest.account_request.accept_terms_and') }}

                            <a
                                href="{{ route('privacy.policy') }}"
                                target="_blank"
                                class="text-navy hover:text-orange-500 font-medium underline"
                            >
                                {{ __('guest.account_request.privacy_policy') }}
                            </a>.
                        </span>
                    </label>

                    <p
                        x-cloak
                        x-show="fieldErrors.accept_terms"
                        class="text-xs text-red-600 mt-1"
                        x-text="fieldErrors.accept_terms"
                    ></p>
                </div>
            </div>

            {{-- ============================================================
                 ERREUR GÉNÉRALE
                 ============================================================ --}}

            <p
                x-show="stepError"
                x-cloak
                class="text-sm text-red-600"
                x-text="stepError"
            ></p>

            {{-- ============================================================
                 NAVIGATION
                 ============================================================ --}}

            <div class="flex gap-2">

                <button
                    type="button"
                    x-show="step > 1"
                    @click="prev()"
                    :disabled="busy || isSubmitting()"
                    class="flex-1 py-2.5 px-4 border border-gray-300 text-gray-700 font-semibold text-sm rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    {{ __('guest.account_request.prev') }}
                </button>

                <button
                    type="button"
                    x-show="step < 4"
                    @click="next()"
                    :disabled="busy"
                    class="flex-1 py-2.5 px-4 bg-[#173052] hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors disabled:opacity-50"
                >
                    <span x-show="!busy">
                        {{ __('guest.account_request.next') }}
                        <i class="fas fa-arrow-right ml-2"></i>
                    </span>

                    <span x-show="busy">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        <span x-text="busyLabel"></span>
                    </span>
                </button>

                <button
                    type="submit"
                    x-show="step === 4"
                    :disabled="isSubmitting()"
                    class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 px-4 min-w-[150px] bg-navy text-white font-semibold text-sm rounded-lg hover:opacity-90 transition disabled:opacity-40 disabled:cursor-not-allowed"
                >
                    <template x-if="!isSubmitting()">
                        <span class="inline-flex items-center gap-2">
                            <i class="fa-solid fa-paper-plane"></i>
                            {{ __('guest.account_request.submit') }}
                        </span>
                    </template>

                    <template x-if="isSubmitting()">
                        <span
                            class="inline-flex items-center gap-2"
                            aria-live="polite"
                        >
                            <i
                                class="fa-solid fa-spinner fa-spin"
                                aria-hidden="true"
                            ></i>
                            {{ __('guest.account_request.submitting') }}
                        </span>
                    </template>
                </button>

            </div>

            {{-- ============================================================
                 CONNEXION
                 ============================================================ --}}

            <p class="text-center text-sm text-gray-500">
                {{ __('guest.account_request.already_have_account') }}

                <a
                    href="{{ route('login') }}"
                    class="text-navy hover:text-orange-500 font-medium"
                >
                    {{ __('guest.account_request.sign_in') }}
                </a>
            </p>
        </form>
    </div>

</x-guest-layout>