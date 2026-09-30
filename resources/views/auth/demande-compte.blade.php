<x-guest-layout :wide="true">
    @php
        $isMineur = (bool) old('is_mineur');
        $userType = old('user_type', 'pensionne');

        $types = [
            'pensionne' => [
                'label' => __('messages.pensioner'),
                'icon' => 'fa-user-clock',
            ],
        ];

        $pieceTypes = [
            'cin' => 'Carte d’identification nationale',
            'passeport' => 'Passeport',
            'permis' => 'Permis de conduire',
        ];

        $serverErrors = collect($errors->messages())
            ->map(fn (array $messages) => $messages[0] ?? '')
            ->all();

        /*
         * Champs par étape — sert au routage des erreurs serveur.
         */
        $step1Fields = [
            'is_mineur',
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
            'email',
            'nif',
            'ninu',
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

        $formState = [
            'step' => $initialStep,
            'isMineur' => $isMineur,
            'userType' => $userType,

            'telephone' => old('telephone', ''),
            'email' => old('email', ''),
            'nif' => old('nif', ''),
            'ninu' => old('ninu', ''),
            'pensionCode' => old('pension_code', ''),
            'adresse' => old('adresse', ''),

            'pieceType' => old('piece_identite_type', ''),
            'representantLien' => old('representant_lien', ''),
            'representantPieceType' => old('piece_identite_representant_type', ''),

            'ocrUrl' => route('demandes.compte.ocr'),
            'availabilityUrl' => route('demandes.compte.disponibilite'),

            'fieldErrors' => $serverErrors,
        ];
    @endphp

    <style>
        [x-cloak] {
            display: none !important;
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
         * FILE INPUT
         * ============================================================ */

        .compte-file {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
            max-width: 100%;
            min-width: 0;
            position: relative;
        }

        .compte-file input[type="file"] {
            position: absolute;
            width: 0;
            height: 0;
            padding: 0;
            margin: 0;
            overflow: hidden;
            opacity: 0;
            pointer-events: none;
            border: 0;
        }

        .compte-file-btn {
            display: inline-flex;
            align-items: center;
            flex-shrink: 0;
            padding: 0.55rem 0.95rem;
            border-radius: 0.5rem;
            background: #173052;
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color .15s ease;
        }

        .compte-file-btn:hover {
            background: #ea580c;
        }

        .compte-file-name {
            flex: 1 1 0;
            min-width: 0;
            font-size: 0.875rem;
            color: #6b7280;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
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

    <div x-data="demandeCompteForm(@js($formState))">

        {{-- ============================================================
             EN-TÊTE
             ============================================================ --}}

        <div class="mb-4 text-center">
            <h2 class="text-xl font-bold text-gray-800">
                Demande de création de compte
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Étape
                <span x-text="currentStepNumber"></span>
                /
                <span x-text="totalSteps"></span>

                <span class="text-gray-300 mx-1">·</span>

                <span x-text="currentStepCaption"></span>
            </p>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                <i class="fas fa-check-circle mr-1"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- ============================================================
             INDICATEUR DES ÉTAPES
             ============================================================ --}}

        <ol class="compte-steps" aria-label="Étapes du formulaire">
            <template x-for="(item, index) in visibleSteps" :key="item.id">
                <li
                    class="compte-step"
                    :class="{
                        'is-complete': item.id < step,
                        'is-current': item.id === step
                    }"
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
            x-ref="form"
            @submit.prevent="onSubmit"
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
                <div class="flex items-start gap-3 border-b border-gray-200 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-gray-800">
                            Téléversement des documents
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Sélectionnez les documents nécessaires à la création du compte.
                            Les informations seront ensuite lues automatiquement par OCR.
                        </p>
                    </div>
                </div>

                {{-- Type de pensionné --}}
                <!-- <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-800 mb-2">
                        Situation du pensionné
                    </p>

                    <label class="flex items-start gap-2 cursor-pointer select-none">
                        <input
                            type="checkbox"
                            id="is_mineur"
                            name="is_mineur"
                            value="1"
                            x-model="isMineur"
                            @change="clearDocumentErrors()"
                            {{ $isMineur ? 'checked' : '' }}
                            class="mt-1 w-4 h-4 rounded border-gray-300 text-navy focus:ring-navy"
                        >

                        <span>
                            <span class="block text-sm font-medium text-gray-700">
                                Pensionné mineur
                            </span>

                            <span class="block text-xs text-gray-500 mt-0.5">
                                Cochez cette case si le compte doit être créé au nom d’un pensionné mineur.
                            </span>
                        </span>
                    </label>
                </div> -->

                {{-- ====================================================
                     MAJEUR
                     ==================================================== --}}

                <div
                    x-show="!isMineur"
                    x-cloak
                    class="compte-document-card required space-y-4"
                >
                    <div>
                        <h4 class="text-sm font-semibold text-gray-800">
                            Pièce d’identité du pensionné
                        </h4>

                        <p class="text-xs text-gray-500 mt-1">
                            Le document doit appartenir au pensionné concerné.
                        </p>
                    </div>

                    <div>
                        <label
                            for="piece_identite_type"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Pièce d’identité
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
                            <option value="">Choisir un document</option>

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
                            Document
                            <span class="text-red-500">*</span>
                        </span>

                        <div class="compte-file">
                            <input
                                type="file"
                                id="piece_identite"
                                name="piece_identite"
                                accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                                @change="
                                    files.piece_identite = $event.target.files[0]?.name || '';
                                    clearFieldError('piece_identite');
                                "
                            >

                            <label
                                for="piece_identite"
                                class="compte-file-btn"
                            >
                                <i class="fas fa-upload mr-2"></i>
                                Choisir un fichier
                            </label>

                            <span
                                class="compte-file-name"
                                x-text="files.piece_identite || 'Aucun fichier choisi'"
                            ></span>
                        </div>

                        <p class="text-xs text-gray-500 mt-1">
                            JPG, PNG, WEBP ou PDF — 5 Mo max.
                        </p>

                        <p
                            x-cloak
                            x-show="fieldErrors.piece_identite"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.piece_identite"
                        ></p>
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
                                Acte de naissance du pensionné mineur
                            </h4>

                            <p class="text-xs text-gray-500 mt-1">
                                L’acte de naissance doit correspondre au pensionné mineur.
                            </p>
                        </div>

                        <div>
                            <span class="block text-sm font-medium text-gray-700 mb-1">
                                Document
                                <span class="text-red-500">*</span>
                            </span>

                            <div class="compte-file">
                                <input
                                    type="file"
                                    id="acte_naissance"
                                    name="acte_naissance"
                                    accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                                    @change="
                                        files.acte_naissance = $event.target.files[0]?.name || '';
                                        clearFieldError('acte_naissance');
                                    "
                                >

                                <label
                                    for="acte_naissance"
                                    class="compte-file-btn"
                                >
                                    <i class="fas fa-upload mr-2"></i>
                                    Choisir un fichier
                                </label>

                                <span
                                    class="compte-file-name"
                                    x-text="files.acte_naissance || 'Aucun fichier choisi'"
                                ></span>
                            </div>

                            <p class="text-xs text-gray-500 mt-1">
                                JPG, PNG, WEBP ou PDF — 5 Mo max.
                            </p>

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
                                Pièce d’identité du représentant légal
                            </h4>

                            <p class="text-xs text-gray-500 mt-1">
                                Le représentant agit pour le compte du pensionné mineur.
                                Le compte reste créé au nom du mineur.
                            </p>
                        </div>

                        <div>
                            <label
                                for="representant_lien"
                                class="block text-sm font-medium text-gray-700 mb-1"
                            >
                                Représentant légal
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
                                <option value="">Choisir</option>

                                <option
                                    value="pere"
                                    @selected(old('representant_lien') === 'pere')
                                >
                                    Père
                                </option>

                                <option
                                    value="mere"
                                    @selected(old('representant_lien') === 'mere')
                                >
                                    Mère
                                </option>

                                <option
                                    value="tuteur"
                                    @selected(old('representant_lien') === 'tuteur')
                                >
                                    Tuteur subrogé
                                </option>
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
                                Type de pièce d’identité
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
                                <option value="">Choisir un document</option>

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

                            <div class="compte-file">
                                <input
                                    type="file"
                                    id="piece_identite_representant"
                                    name="piece_identite_representant"
                                    accept="image/jpeg,image/png,image/webp,application/pdf,.jpg,.jpeg,.png,.webp,.pdf"
                                    @change="
                                        files.piece_identite_representant = $event.target.files[0]?.name || '';
                                        clearFieldError('piece_identite_representant');
                                    "
                                >

                                <label
                                    for="piece_identite_representant"
                                    class="compte-file-btn"
                                >
                                    <i class="fas fa-upload mr-2"></i>
                                    Choisir un fichier
                                </label>

                                <span
                                    class="compte-file-name"
                                    x-text="files.piece_identite_representant || 'Aucun fichier choisi'"
                                ></span>
                            </div>

                            <p class="text-xs text-gray-500 mt-1">
                                JPG, PNG, WEBP ou PDF — 5 Mo max.
                            </p>

                            <p
                                x-cloak
                                x-show="fieldErrors.piece_identite_representant"
                                class="text-xs text-red-600 mt-1"
                                x-text="fieldErrors.piece_identite_representant"
                            ></p>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-blue-50 border border-blue-100 p-3">
                    <div class="flex items-start gap-2">
                        <i class="fas fa-info-circle text-[#173052] mt-0.5"></i>

                        <p class="text-xs text-gray-600">
                            Après avoir sélectionné les documents, cliquez sur
                            <strong>Suivant</strong>.
                            Le portail procédera automatiquement à leur lecture OCR.
                        </p>
                    </div>
                </div>
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
                            Lecture et vérification OCR
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Les informations sont extraites automatiquement des documents.
                            Vérifiez-les et corrigez-les si nécessaire.
                        </p>
                    </div>
                </div>

                <template x-if="ocrDocuments.length === 0">
                    <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5"></i>

                            <div>
                                <p class="text-sm font-semibold text-yellow-800">
                                    Aucun résultat OCR disponible
                                </p>

                                <p class="text-xs text-yellow-700 mt-1">
                                    Vous pouvez revenir à l’étape précédente pour vérifier les documents.
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
                                    Lecture automatique impossible.
                                    Vous pouvez saisir les informations manuellement.
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
                                    Document
                                </p>

                                <div class="compte-ocr-preview">

                                    <img
                                        x-show="previews[doc.key] && !previews[doc.key].pdf"
                                        :src="previews[doc.key] && previews[doc.key].url"
                                        :alt="doc.label"
                                    >

                                    <iframe
                                        x-show="previews[doc.key] && previews[doc.key].pdf"
                                        :src="previews[doc.key] && previews[doc.key].url"
                                        class="w-full min-h-[16rem]"
                                        title="Aperçu PDF"
                                    ></iframe>

                                    <p
                                        x-show="!previews[doc.key]"
                                        class="text-sm text-gray-400 px-4 text-center"
                                    >
                                        Aperçu indisponible
                                    </p>
                                </div>
                            </div>

                            {{-- Données OCR --}}
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">
                                    Informations extraites
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
                        Les données OCR pourront être consultées dans le récapitulatif
                        avant l’envoi définitif de la demande.
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
                            Informations du compte
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Complétez les informations qui serviront à créer votre compte.
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
                                    :class="userType === '{{ $value }}'
                                        ? 'border-blue-500 bg-blue-50 text-blue-700'
                                        : 'border-gray-200 text-gray-600 hover:border-gray-300'"
                                >
                                    <input
                                        type="radio"
                                        id="user_type_{{ $value }}"
                                        name="user_type"
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
                        <label
                            for="telephone"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Numéro de téléphone
                            <span class="text-red-500">*</span>
                        </label>

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
                            aria-describedby="telephone-help telephone-error"
                        >

                        <p
                            id="telephone-help"
                            class="text-xs text-gray-500 mt-1"
                        >
                            Format international obligatoire avec l’indicatif pays.
                            Ex. : +50938123456, +33612345678, +14161234567
                        </p>

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
                        <label
                            for="nif"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            NIF
                        </label>

                        <input
                            id="nif"
                            type="text"
                            name="nif"
                            x-model="nif"
                            @input="clearFieldError('nif'); scheduleAvailabilityCheck('nif')"
                            placeholder="000-000-000-0"
                            inputmode="numeric"
                            autocomplete="off"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('nif')"
                        >

                        <p
                            x-show="!fieldErrors.nif"
                            class="text-xs mt-1"
                            :class="nifStatus === 'error'
                                ? 'text-red-600'
                                : (nifStatus === 'ok'
                                    ? 'text-green-700'
                                    : 'text-gray-400')"
                            x-text="nifMessage"
                        ></p>

                        <p
                            x-cloak
                            x-show="fieldErrors.nif"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.nif"
                        ></p>
                    </div>

                    {{-- NINU --}}
                    <div>
                        <label
                            for="ninu"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            NINU
                        </label>

                        <input
                            id="ninu"
                            type="text"
                            name="ninu"
                            x-model="ninu"
                            @input="clearFieldError('ninu')"
                            placeholder="7-12345 ou 8-12345"
                            autocomplete="off"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('ninu')"
                        >

                        <p
                            x-cloak
                            x-show="fieldErrors.ninu"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.ninu"
                        ></p>
                    </div>

                    {{-- Code pension (optionnel) --}}
                    <div x-show="userType === 'pensionne'">
                        <label
                            for="pension_code"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Code pension
                            <span class="text-xs font-normal text-gray-400">(si code pension)</span>
                        </label>

                        <input
                            id="pension_code"
                            type="text"
                            name="pension_code"
                            x-model="pension_code"
                            @input="clearFieldError('pension_code'); scheduleAvailabilityCheck('pension_code')"
                            placeholder="8-34321"
                            inputmode="numeric"
                            autocomplete="off"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('pension_code')"
                        >

                        <p
                            x-show="!fieldErrors.pension_code"
                            class="text-xs mt-1"
                            :class="pension_codeStatus === 'error'
                                ? 'text-red-600'
                                : (pension_codeStatus === 'ok'
                                    ? 'text-green-700'
                                    : 'text-gray-400')"
                            x-text="pension_codeMessage"
                        ></p>

                        <p
                            x-cloak
                            x-show="fieldErrors.pension_code"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.pension_code"
                        ></p>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label
                            for="email"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Adresse e-mail
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            x-model="email"
                            @input="clearFieldError('email'); scheduleAvailabilityCheck('email')"
                            placeholder="exemple@gmail.com"
                            autocomplete="email"
                            class="w-full py-2.5 px-3 border rounded-lg text-sm focus:outline-none focus:ring-2"
                            :class="borderClass('email')"
                        >

                        <p
                            x-show="!fieldErrors.email && emailMessage"
                            x-cloak
                            class="text-xs mt-1"
                            :class="emailStatus === 'error'
                                ? 'text-red-600'
                                : 'text-green-700'"
                            x-text="emailMessage"
                        ></p>

                        <p
                            x-cloak
                            x-show="fieldErrors.email"
                            class="text-xs text-red-600 mt-1"
                            x-text="fieldErrors.email"
                        ></p>
                    </div>

                    {{-- Mot de passe --}}
                    <div>
                        <label
                            for="password"
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Mot de passe
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
                                    ? 'Masquer le mot de passe'
                                    : 'Afficher le mot de passe'"
                            >
                                <i
                                    :class="showPassword
                                        ? 'fas fa-eye-slash'
                                        : 'fas fa-eye'"
                                    class="text-sm"
                                ></i>
                            </button>
                        </div>

                        <p
                            x-show="!fieldErrors.password"
                            class="text-xs text-gray-400 mt-1"
                        >
                            Au moins 8 caractères. Vous l’utiliserez pour vous connecter.
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
                            Adresse actuelle
                            <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            id="adresse"
                            name="adresse"
                            rows="2"
                            x-model="adresse"
                            @input="clearFieldError('adresse')"
                            placeholder="Rue, commune, département…"
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
                            Récapitulatif et validation
                        </h3>

                        <p class="text-xs text-gray-500 mt-0.5">
                            Vérifiez les informations ci-dessous puis validez votre demande.
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
                                Informations du compte
                                <span class="compte-collapsible-sub">
                                    Identité, contacts et connexion
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
                                <span class="text-gray-500">Type de compte :</span>
                                <span x-text="userTypeLabel"></span>
                            </p>

                            <p>
                                <span class="text-gray-500">Téléphone :</span>
                                <span x-text="telephone || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500">NIF :</span>
                                <span x-text="nif || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500">NINU :</span>
                                <span x-text="ninu || '—'"></span>
                            </p>

                            <p x-show="userType === 'pensionne'">
                                <span class="text-gray-500">Code pension :</span>
                                <span x-text="pension_code || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500">E-mail :</span>
                                <span x-text="email || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500">Mot de passe :</span>
                                défini
                            </p>

                            <p>
                                <span class="text-gray-500">Adresse actuelle :</span>
                                <span x-text="adresse || '—'"></span>
                            </p>

                            <p>
                                <span class="text-gray-500">Pensionné mineur :</span>
                                <span x-text="isMineur ? 'Oui' : 'Non'"></span>
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
                                Pièces justificatives
                                <span class="compte-collapsible-sub">
                                    Documents joints à la demande
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
                                        <span class="text-gray-500">
                                            Pièce d’identité :
                                        </span>

                                        <span x-text="pieceTypeLabel"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500">
                                            Fichier :
                                        </span>

                                        <span x-text="files.piece_identite || 'À joindre'"></span>
                                    </p>
                                </div>
                            </template>

                            <template x-if="isMineur">
                                <div class="space-y-1">
                                    <p>
                                        <span class="text-gray-500">
                                            Acte de naissance :
                                        </span>

                                        <span x-text="files.acte_naissance || 'À joindre'"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500">
                                            Représentant :
                                        </span>

                                        <span x-text="representantLienLabel"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500">
                                            Pièce du représentant :
                                        </span>

                                        <span x-text="representantPieceTypeLabel"></span>
                                    </p>

                                    <p>
                                        <span class="text-gray-500">
                                            Fichier :
                                        </span>

                                        <span x-text="files.piece_identite_representant || 'À joindre'"></span>
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
                                Informations extraites par OCR
                                <span class="compte-collapsible-sub">
                                    Détails lus sur les documents
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
                                    Ce document n’a pas pu être lu automatiquement.
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
                            J’accepte les

                            <a
                                href="{{ route('terms.policy') }}"
                                target="_blank"
                                class="text-navy hover:text-orange-500 font-medium underline"
                            >
                                conditions d’utilisation
                            </a>

                            et la

                            <a
                                href="{{ route('privacy.policy') }}"
                                target="_blank"
                                class="text-navy hover:text-orange-500 font-medium underline"
                            >
                                politique de confidentialité
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
                    :disabled="busy"
                    class="flex-1 py-2.5 px-4 border border-gray-300 text-gray-700 font-semibold text-sm rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
                >
                    <i class="fas fa-arrow-left mr-2"></i>
                    Précédent
                </button>

                <button
                    type="button"
                    x-show="step < 4"
                    @click="next()"
                    :disabled="busy"
                    class="flex-1 py-2.5 px-4 bg-[#173052] hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors disabled:opacity-50"
                >
                    <span x-show="!busy">
                        Suivant
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
                    :disabled="busy"
                    class="flex-1 py-2.5 px-4 bg-[#173052] hover:bg-orange-600 text-white font-semibold text-sm rounded-lg transition-colors disabled:opacity-50"
                >
                    <i class="fas fa-paper-plane mr-2"></i>
                    Soumettre
                </button>

            </div>

            {{-- ============================================================
                 CONNEXION
                 ============================================================ --}}

            <p class="text-center text-sm text-gray-500">
                Vous avez déjà un compte ?

                <a
                    href="{{ route('login') }}"
                    class="text-navy hover:text-orange-500 font-medium"
                >
                    Se connecter
                </a>
            </p>

            {{-- Retour accueil --}}
            <div class="pt-2 text-center">
                <a
                    href="{{ url('/') }}"
                    class="inline-flex items-center gap-2 text-sm text-gray-600 hover:text-[#173052] font-medium transition-colors"
                >
                    <i class="fas fa-home text-xs"></i>
                    <span>Retour à l’accueil</span>
                </a>
            </div>
        </form>
    </div>

    <script>
        function demandeCompteForm(initial) {
            const pieceLabels = {
                cin: 'Carte d’identification nationale',
                passeport: 'Passeport',
                permis: 'Permis de conduire',
                acte_naissance: 'Acte de naissance',
            };

            const lienLabels = {
                pere: 'Père',
                mere: 'Mère',
                tuteur: 'Tuteur subrogé',
            };

            const fieldValue = (id) =>
                document.getElementById(id)?.value?.trim() || '';

            const hasFile = (id) => {
                const field = document.getElementById(id);

                return !!(
                    field &&
                    field.files &&
                    field.files.length > 0
                );
            };

            return {
                /* ========================================================
                 * ÉTAT
                 * ======================================================== */

                step: initial.step || 1,

                isMineur: !!initial.isMineur,

                userType: initial.userType || 'pensionne',

                telephone: initial.telephone || '',
                email: initial.email || '',
                nif: initial.nif || '',
                ninu: initial.ninu || '',
                pension_code: initial.pensionCode || '',
                adresse: initial.adresse || '',

                pieceType: initial.pieceType || '',
                representantLien: initial.representantLien || '',
                representantPieceType: initial.representantPieceType || '',

                acceptTerms: {{ old('accept_terms') ? 'true' : 'false' }},

                showPassword: false,

                fieldErrors: initial.fieldErrors || {},

                stepError: '',

                busy: false,
                busyLabel: '',
                idempotencyKey: (() => {
                    const STORE = 'demande_compte_idem';
                    let k = sessionStorage.getItem(STORE);
                    if (!k) {
                        k = (crypto.randomUUID?.())
                            || (Date.now().toString(36) + Math.random().toString(36).slice(2));
                        sessionStorage.setItem(STORE, k);
                    }
                    return k;
                })(),

                _redirecting: false,

                ocrUrl: initial.ocrUrl,
                availabilityUrl: initial.availabilityUrl,

                /* ========================================================
                 * BLOCS DÉPLIABLES (ÉTAPE 4)
                 * ======================================================== */

                openSections: {
                    resume: true,
                    pieces: false,
                    ocr: true,
                },

                /* ========================================================
                 * DISPONIBILITÉ
                 * ======================================================== */

                nifStatus: '',
                nifMessage: '',

                pension_codeStatus: '',
                pension_codeMessage: '',

                emailStatus: '',
                emailMessage: '',

                _checkTimers: {
                    nif: null,
                    pension_code: null,
                    email: null,
                },

                _checkSeq: {
                    nif: 0,
                    pension_code: 0,
                    email: 0,
                },

                /* ========================================================
                 * OCR
                 * ======================================================== */

                ocrDocuments: [],
                ocrFields: {},

                previews: {},

                files: {
                    piece_identite: '',
                    acte_naissance: '',
                    piece_identite_representant: '',
                },

                schemas: {
                    cin: [
                        'numero_carte',
                        'prenom',
                        'nom',
                        'sexe',
                        'nationalite',
                        'date_naissance',
                        'lieu_naissance',
                        'date_emission',
                        'date_expiration',
                        'numero_identification_unique',
                    ],

                    passeport: [
                        'numero_passeport',
                        'prenom',
                        'nom',
                        'nationalite',
                        'nif',
                        'taille',
                        'numero_personnel',
                        'date_naissance',
                        'lieu_naissance',
                        'sexe',
                        'can',
                        'date_emission',
                        'date_expiration',
                    ],

                    permis: [
                        'dossier',
                        'nif',
                        'nom',
                        'prenom',
                        'adresse',
                        'date_naissance',
                        'type',
                        'sexe',
                        'groupe_sanguin',
                        'lieu_emission',
                        'emis_le',
                        'expire_le',
                    ],

                    acte_naissance: [
                        'numero_acte',
                        'annee_acte',
                        'registre',
                        'prenom',
                        'nom',
                        'sexe',
                        'date_naissance',
                        'date_naissance_texte',
                        'heure_naissance',
                        'lieu_naissance',
                        'nom_pere',
                        'nom_mere',
                        'commune',
                        'section_communale',
                        'officier_etat_civil',
                        'temoins',
                        'date_acte',
                        'lieu_delivrance',
                        'date_delivrance',
                        'autorite_delivrance',
                    ],
                },

                /* ========================================================
                 * ÉTAPES
                 * ======================================================== */

                steps: [
                    {
                        id: 1,
                        label: 'Documents',
                        caption: 'Téléversement',
                    },
                    {
                        id: 2,
                        label: 'OCR',
                        caption: 'Vérification',
                    },
                    {
                        id: 3,
                        label: 'Informations',
                        caption: 'Compte',
                    },
                    {
                        id: 4,
                        label: 'Validation',
                        caption: 'Récapitulatif',
                    },
                ],

                fieldLabels: {
                    nif: 'NIF',
                    code_pension: 'Code pension',
                    nom: 'Nom',
                    prenom: 'Prénom',
                    email: 'E-mail',
                    telephone: 'Téléphone',
                    adresse: 'Adresse',
                    dossier: 'Dossier',
                    date_naissance: 'Date de naissance',
                    type: 'Type',
                    sexe: 'Sexe',
                    groupe_sanguin: 'Groupe sanguin',
                    lieu_emission: 'Lieu d’émission',
                    emis_le: 'Émis le',
                    expire_le: 'Expire le',
                    numero_carte: 'Numéro de carte',
                    nationalite: 'Nationalité',
                    lieu_naissance: 'Lieu de naissance',
                    date_emission: 'Date d’émission',
                    date_expiration: 'Date d’expiration',
                    numero_identification_unique: 'N° d’identification unique',
                    numero_passeport: 'Numéro de passeport',
                    taille: 'Taille',
                    numero_personnel: 'Numéro personnel',
                    can: 'CAN',
                    numero_acte: 'Numéro d’acte',
                    nom_pere: 'Nom du père',
                    nom_mere: 'Nom de la mère',
                    commune: 'Commune',
                    date_acte: 'Date de l’acte',
                    annee_acte: 'Année',
                    registre: 'Registre',
                    date_naissance_texte: 'Date de naissance (en lettres)',
                    heure_naissance: 'Heure de naissance',
                    section_communale: 'Section communale',
                    officier_etat_civil: 'Officier d’état civil',
                    temoins: 'Témoins',
                    lieu_delivrance: 'Lieu de délivrance',
                    date_delivrance: 'Date de délivrance',
                    autorite_delivrance: 'Autorité de délivrance',
                },

                /* ========================================================
                 * GETTERS
                 * ======================================================== */

                get visibleSteps() {
                    return this.steps;
                },

                get totalSteps() {
                    return this.visibleSteps.length;
                },

                get currentStepNumber() {
                    const index = this.visibleSteps.findIndex(
                        (item) => item.id === this.step
                    );

                    return index >= 0
                        ? index + 1
                        : this.step;
                },

                get currentStepCaption() {
                    const current =
                        this.visibleSteps.find(
                            (item) => item.id === this.step
                        ) ||
                        this.steps.find(
                            (item) => item.id === this.step
                        );

                    return current?.caption ||
                        current?.label ||
                        '';
                },

                get userTypeLabel() {
                    return this.userType === 'pensionne'
                        ? 'Pensionné'
                        : this.userType;
                },

                get pieceTypeLabel() {
                    return pieceLabels[this.pieceType] || '—';
                },

                get representantLienLabel() {
                    return lienLabels[this.representantLien] || '—';
                },

                get representantPieceTypeLabel() {
                    return pieceLabels[this.representantPieceType] || '—';
                },

                /* ========================================================
                 * INIT
                 * ======================================================== */

                init() {
                    if ((this.nif || '').trim()) {
                        this.scheduleAvailabilityCheck('nif');
                    }

                    if (
                        this.userType === 'pensionne' &&
                        (this.pension_code || '').trim()
                    ) {
                        this.scheduleAvailabilityCheck('pension_code');
                    }

                    if ((this.email || '').trim()) {
                        this.scheduleAvailabilityCheck('email');
                    }

                    /*
                     * Synchronise les fichiers présents après un retour
                     * de validation Laravel.
                     */
                    this.syncFromForm();
                },

                /* ========================================================
                 * ERREURS
                 * ======================================================== */

                fieldBorderClass(field) {
                    return this.borderClass(field);
                },

                borderClass(field) {
                    if (
                        this.fieldErrors[field] ||
                        this[field + 'Status'] === 'error'
                    ) {
                        return 'border-red-400 focus:ring-red-400';
                    }

                    if (this[field + 'Status'] === 'ok') {
                        return 'border-green-400 focus:ring-green-500';
                    }

                    return 'border-gray-300 focus:ring-blue-500';
                },

                clearFieldError(field) {
                    if (this.fieldErrors[field]) {
                        this.fieldErrors[field] = '';
                    }
                },

                clearDocumentErrors() {
                    [
                        'piece_identite_type',
                        'piece_identite',
                        'acte_naissance',
                        'representant_lien',
                        'piece_identite_representant_type',
                        'piece_identite_representant',
                    ].forEach((field) => {
                        this.clearFieldError(field);
                    });
                },

                setFieldError(field, message) {
                    this.fieldErrors = {
                        ...this.fieldErrors,
                        [field]: message || '',
                    };
                },

                hasFieldErrors() {
                    return Object.values(this.fieldErrors)
                        .some((message) => !!message);
                },

                /* ========================================================
                 * TÉLÉPHONE
                 * ======================================================== */

                normalizeTelephone() {
                    this.telephone =
                        (this.telephone || '').replace(/\s+/g, '');
                },

                /* ========================================================
                 * DISPONIBILITÉ
                 * ======================================================== */

                scheduleAvailabilityCheck(field) {
                    clearTimeout(this._checkTimers[field]);

                    const value = (this[field] || '').trim();

                    this[field + 'Message'] = '';

                    if (field === 'email' && value === '') {
                        this.emailStatus = '';
                        return;
                    }

                    if (field === 'nif') {
                        if (value === '') {
                            this.nifStatus = '';
                            this.nifMessage = '';
                            return;
                        }

                        if (!/^\d{3}-\d{3}-\d{3}-\d$/.test(value)) {
                            this.nifStatus = 'error';
                            this.nifMessage =
                                'Le NIF doit être au format 000-000-000-0.';
                            return;
                        }
                    }

                    if (field === 'pension_code') {
                        /*
                         * Code pension désormais optionnel :
                         * s'il est vide, aucune vérification.
                         * S'il est rempli, on valide juste le format.
                         */
                        if (value === '') {
                            this.pension_codeStatus = '';
                            this.pension_codeMessage = '';
                            return;
                        }

                        if (!/^\d-\d{5}$/.test(value)) {
                            this.pension_codeStatus = 'error';
                            this.pension_codeMessage =
                                'Le code pension doit être au format 0-00000 (ex. 8-34321).';
                            return;
                        }
                    }

                    this[field + 'Status'] = 'checking';

                    this._checkTimers[field] = setTimeout(
                        () => this.checkAvailability(field),
                        250
                    );
                },

                async checkAvailability(field) {
                    const seq = ++this._checkSeq[field];

                    const value = (this[field] || '').trim();

                    try {
                        const url =
                            this.availabilityUrl +
                            '?' +
                            new URLSearchParams({
                                field,
                                value,
                            }).toString();

                        const response = await fetch(url, {
                            headers: {
                                Accept: 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (seq !== this._checkSeq[field]) {
                            return data;
                        }

                        if (data.empty && field === 'email') {
                            this.emailStatus = '';
                            this.emailMessage = '';
                            return data;
                        }

                        this[field + 'Status'] =
                            data.available
                                ? 'ok'
                                : 'error';

                        this[field + 'Message'] =
                            data.message || '';

                        return data;

                    } catch (error) {
                        if (seq !== this._checkSeq[field]) {
                            return null;
                        }

                        this[field + 'Status'] = '';

                        return null;
                    }
                },

                /* ========================================================
                 * OCR
                 * ======================================================== */

                fieldLabel(key) {
                    return this.fieldLabels[key] ||
                        key.replaceAll('_', ' ');
                },

                pieceTypeName(type) {
                    return pieceLabels[type] || type || '';
                },

                declaredTypeFor(key) {
                    if (key === 'piece_identite') {
                        return this.pieceType;
                    }

                    if (key === 'piece_identite_representant') {
                        return this.representantPieceType;
                    }

                    if (key === 'acte_naissance') {
                        return 'acte_naissance';
                    }

                    return '';
                },

                fieldsFor(doc) {
                    const type =
                        doc.fields?.type_document ||
                        this.declaredTypeFor(doc.key);

                    let keys = this.schemas[type] || [];

                    if (keys.length === 0) {
                        keys = Object.keys(
                            doc.fields || {}
                        ).filter(
                            (key) => key !== 'type_document'
                        );
                    }

                    if (keys.length === 0) {
                        keys = [
                            'nom',
                            'prenom',
                            'date_naissance',
                            'nif',
                            'adresse',
                        ];
                    }

                    return keys.map((key) => ({
                        key,
                        label: this.fieldLabel(key),
                    }));
                },

                hydrateDoc(doc) {
                    const type =
                        doc.fields?.type_document ||
                        this.declaredTypeFor(doc.key) ||
                        '';

                    const fields = {
                        ...(doc.fields || {}),
                    };

                    if (type) {
                        fields.type_document = type;
                    }

                    this.fieldsFor({
                        ...doc,
                        fields,
                    }).forEach(({ key }) => {
                        if (fields[key] == null) {
                            fields[key] = '';
                        }
                    });

                    return {
                        ...doc,
                        fields,
                    };
                },

                revokePreviews() {
                    Object.values(this.previews)
                        .forEach((preview) => {
                            if (preview?.url) {
                                URL.revokeObjectURL(
                                    preview.url
                                );
                            }
                        });

                    this.previews = {};
                },

                buildPreviews() {
                    this.revokePreviews();

                    const next = {};

                    const keys = this.isMineur
                        ? [
                            'acte_naissance',
                            'piece_identite_representant',
                        ]
                        : [
                            'piece_identite',
                        ];

                    keys.forEach((key) => {
                        const file =
                            document.getElementById(key)
                                ?.files?.[0];

                        if (!file) {
                            return;
                        }

                        const pdf =
                            file.type === 'application/pdf' ||
                            /\.pdf$/i.test(file.name);

                        if (
                            file.type.startsWith('image/') ||
                            pdf
                        ) {
                            next[key] = {
                                url: URL.createObjectURL(file),
                                pdf,
                            };
                        }
                    });

                    this.previews = next;
                },

                syncOcrFields() {
                    const merged = {};

                    const ordered = [
                        ...this.ocrDocuments,
                    ].sort((left, right) => {
                        if (left.key === 'acte_naissance') {
                            return 1;
                        }

                        if (right.key === 'acte_naissance') {
                            return -1;
                        }

                        return 0;
                    });

                    ordered.forEach((doc) => {
                        Object.entries(
                            doc.fields || {}
                        ).forEach(([key, value]) => {
                            if (key === 'type_document') {
                                return;
                            }

                            const trimmed =
                                String(value ?? '').trim();

                            if (trimmed !== '') {
                                merged[key] = trimmed;
                            }
                        });
                    });

                    this.ocrFields = merged;
                },

                applyOcr(payload) {
                    this.ocrDocuments =
                        (payload?.documents || [])
                            .map((doc) =>
                                this.hydrateDoc(doc)
                            );

                    this.syncOcrFields();

                    this.buildPreviews();
                },

                csrfToken() {
                    return document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.content || '';
                },

                firstError(payload) {
                    if (
                        payload?.message &&
                        !payload?.errors
                    ) {
                        return payload.message;
                    }

                    const errors =
                        payload?.errors || {};

                    const first =
                        Object.values(errors)[0];

                    if (Array.isArray(first)) {
                        return first[0];
                    }

                    return payload?.message ||
                        'Une erreur est survenue.';
                },

                async postRequest(url, body) {
                    const isFormData =
                        body instanceof FormData;

                    const response = await fetch(url, {
                        method: 'POST',

                        headers: {
                            'X-CSRF-TOKEN':
                                this.csrfToken(),

                            'X-Requested-With':
                                'XMLHttpRequest',

                            'Accept':
                                'application/json',

                            ...(isFormData
                                ? {}
                                : {
                                    'Content-Type':
                                        'application/json',
                                }),
                        },

                        body: isFormData
                            ? body
                            : JSON.stringify(body),
                    });

                    const data =
                        await response
                            .json()
                            .catch(() => ({}));

                    if (!response.ok) {
                        throw data;
                    }

                    return data;
                },

                async runOcr() {
                    this.busy = true;
                    this.busyLabel = 'Lecture OCR…';
                    this.stepError = '';

                    this.buildPreviews();

                    try {
                        const formData = new FormData();

                        formData.append(
                            'is_mineur',
                            this.isMineur ? '1' : '0'
                        );

                        if (!this.isMineur) {
                            const pieceType =
                                document.getElementById(
                                    'piece_identite_type'
                                )?.value || '';

                            const pieceFile =
                                document.getElementById(
                                    'piece_identite'
                                )?.files?.[0];

                            if (pieceType) {
                                formData.append(
                                    'piece_identite_type',
                                    pieceType
                                );
                            }

                            if (pieceFile) {
                                formData.append(
                                    'piece_identite',
                                    pieceFile
                                );
                            }
                        }

                        if (this.isMineur) {
                            const representantLien =
                                document.getElementById(
                                    'representant_lien'
                                )?.value || '';

                            const representantPieceType =
                                document.getElementById(
                                    'piece_identite_representant_type'
                                )?.value || '';

                            const acteNaissance =
                                document.getElementById(
                                    'acte_naissance'
                                )?.files?.[0];

                            const pieceRepresentant =
                                document.getElementById(
                                    'piece_identite_representant'
                                )?.files?.[0];

                            if (representantLien) {
                                formData.append(
                                    'representant_lien',
                                    representantLien
                                );
                            }

                            if (representantPieceType) {
                                formData.append(
                                    'piece_identite_representant_type',
                                    representantPieceType
                                );
                            }

                            if (acteNaissance) {
                                formData.append(
                                    'acte_naissance',
                                    acteNaissance
                                );
                            }

                            if (pieceRepresentant) {
                                formData.append(
                                    'piece_identite_representant',
                                    pieceRepresentant
                                );
                            }
                        }

                        /*
                        * IMPORTANT :
                        * On n'envoie PAS les informations du compte
                        * (téléphone, email, nif, ninu, pension_code,
                        * password, adresse, accept_terms).
                        * Ces informations appartiennent à l'étape 3.
                        */

                        const data = await this.postRequest(
                            this.ocrUrl,
                            formData
                        );

                        this.applyOcr(data);

                        return true;

                    } catch (error) {
                        /*
                         * Le backend renvoie un 422 contenant malgré tout
                         * les `documents` OCR extraits (avec un flag
                         * `error` par document). On hydrate donc l'état
                         * pour permettre à l'utilisateur de corriger
                         * manuellement les champs, puis on avance quand
                         * même à l'étape 2 si au moins un document a pu
                         * être récupéré.
                         */
                        this.applyOcr(error);

                        const message =
                            this.firstError(error);

                        this.setFieldError(
                            this.isMineur
                                ? 'acte_naissance'
                                : 'piece_identite',
                            message
                        );

                        this.stepError = '';

                        return this.ocrDocuments.length > 0;

                    } finally {
                        this.busy = false;
                        this.busyLabel = '';
                    }
                },

                /* ========================================================
                 * SYNCHRONISATION FORMULAIRE
                 * ======================================================== */

                syncFromForm() {
                    this.userType =
                        document.querySelector(
                            'input[name="user_type"]:checked'
                        )?.value || this.userType;

                    this.telephone =
                        fieldValue('telephone');

                    this.email =
                        fieldValue('email');

                    this.nif =
                        fieldValue('nif');

                    this.ninu =
                        fieldValue('ninu');

                    this.pension_code =
                        fieldValue('pension_code');

                    this.adresse =
                        fieldValue('adresse');

                    this.isMineur =
                        !!document.getElementById(
                            'is_mineur'
                        )?.checked;

                    this.pieceType =
                        fieldValue(
                            'piece_identite_type'
                        );

                    this.representantLien =
                        fieldValue(
                            'representant_lien'
                        );

                    this.representantPieceType =
                        fieldValue(
                            'piece_identite_representant_type'
                        );

                    this.files.piece_identite =
                        document.getElementById(
                            'piece_identite'
                        )?.files?.[0]?.name || '';

                    this.files.acte_naissance =
                        document.getElementById(
                            'acte_naissance'
                        )?.files?.[0]?.name || '';

                    this.files.piece_identite_representant =
                        document.getElementById(
                            'piece_identite_representant'
                        )?.files?.[0]?.name || '';
                },

                /* ========================================================
                 * NAVIGATION
                 * ======================================================== */

                goTo(id) {
                    if (
                        id < this.step &&
                        !this.busy
                    ) {
                        this.stepError = '';
                        this.step = id;
                    }
                },

                prev() {
                    this.stepError = '';

                    if (this.step > 1) {
                        this.step -= 1;
                    }
                },

                async next() {
                    if (this.busy) {
                        return;
                    }

                    this.stepError = '';

                    /*
                     * ÉTAPE 1 :
                     * validation des documents, puis OCR.
                     */
                    if (this.step === 1) {
                        this.syncFromForm();

                        if (!this.validateDocuments()) {
                            return;
                        }

                        if (await this.runOcr()) {
                            this.step = 2;
                        }

                        return;
                    }

                    /*
                     * ÉTAPE 2 :
                     * données OCR vérifiées → informations du compte.
                     */
                    if (this.step === 2) {
                        this.syncOcrFields();
                        this.syncFromForm();

                        this.step = 3;

                        return;
                    }

                    /*
                     * ÉTAPE 3 :
                     * validation des informations → récapitulatif.
                     */
                    if (this.step === 3) {
                        this.syncFromForm();

                        if (!this.validateAccount()) {
                            return;
                        }

                        this.step = 4;

                        return;
                    }
                },

                /* ========================================================
                 * SOUMISSION
                 * ======================================================== */

                async onSubmit() {
                    this.syncFromForm();

                    if (!this.validateDocuments()) {
                        this.step = 1;
                        return;
                    }

                    if (!this.validateAccount()) {
                        this.step = 3;
                        return;
                    }

                    if (!this.validateTerms()) {
                        this.step = 4;
                        return;
                    }

                    this.syncOcrFields();

                    const form = this.$refs.form;
                    const formData = new FormData(form);

                    formData.set(
                        'ocr_documents_json',
                        JSON.stringify(this.ocrDocuments)
                    );

                    formData.set('idempotency_key', this.idempotencyKey);

                    this.busy = true;
                    this.busyLabel = 'Envoi…';
                    this.stepError = '';
                    this._redirecting = false;

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': this.csrfToken(),
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                            },
                            body: formData,
                        });

                        /* ── 422 : validation ── */
                        if (response.status === 422) {
                            const data = await response.json().catch(() => ({}));
                            const errors = data.errors || {};

                            this.applyServerErrors(errors);
                            this.step = this.stepFromServerErrors(errors);

                            return;
                        }

                        /* ── 2xx : redirect, never silently ── */
                        if (response.ok) {
                            const raw = await response.text();
                            let data = {};
                            try { data = JSON.parse(raw); } catch { /* HTML or empty */ }

                            const url = data.redirect_url
                                || "{{ route('demandes.rencontre.create') }}";

                            if (!data.redirect_url) {
                                console.error('Server 2xx without redirect_url', raw);
                            }

                            this._redirecting = true;
                            sessionStorage.removeItem('demande_compte_idem');
                            window.location.assign(url);
                            return;
                        }

                        /* ── Other error ── */
                        const data = await response.json().catch(() => ({}));
                        this.stepError = data.message
                            || 'Une erreur est survenue lors de l’envoi.';

                    } catch (e) {
                        this.stepError =
                            'Impossible de contacter le serveur. Vérifiez votre connexion.';
                    } finally {
                        if (!this._redirecting) {
                            this.busy = false;
                            this.busyLabel = '';
                        }
                    }
                },

                /* ========================================================
                 * ROUTAGE DES ERREURS SERVEUR
                 * ======================================================== */

                applyServerErrors(errors) {
                    const flat = {};

                    Object.entries(errors || {}).forEach(([field, messages]) => {
                        flat[field] = Array.isArray(messages)
                            ? (messages[0] || '')
                            : String(messages);
                    });

                    this.fieldErrors = { ...this.fieldErrors, ...flat };
                },

                stepFromServerErrors(errors) {
                    const keys = Object.keys(errors || {});

                    const step1Fields = [
                        'is_mineur',
                        'piece_identite_type',
                        'piece_identite',
                        'acte_naissance',
                        'representant_lien',
                        'piece_identite_representant_type',
                        'piece_identite_representant',
                    ];

                    const step2Fields = ['ocr_documents_json'];

                    const step3Fields = [
                        'user_type',
                        'telephone',
                        'email',
                        'nif',
                        'ninu',
                        'pension_code',
                        'password',
                        'adresse',
                    ];

                    const step4Fields = ['accept_terms'];

                    if (keys.some((k) => step1Fields.includes(k))) return 1;
                    if (keys.some((k) => step2Fields.includes(k))) return 2;
                    if (keys.some((k) => step3Fields.includes(k))) return 3;
                    if (keys.some((k) => step4Fields.includes(k))) return 4;

                    return this.step;
                },

                /* ========================================================
                 * VALIDATION ÉTAPE 1
                 * ======================================================== */

                validateDocuments() {
                    this.syncFromForm();

                    const nextErrors = {};

                    if (!this.isMineur) {

                        if (!this.pieceType) {
                            nextErrors.piece_identite_type =
                                'Veuillez choisir un type de pièce d’identité.';
                        }

                        if (!hasFile('piece_identite')) {
                            nextErrors.piece_identite =
                                'Veuillez télécharger la pièce d’identité.';
                        }

                    } else {

                        if (!hasFile('acte_naissance')) {
                            nextErrors.acte_naissance =
                                'Veuillez télécharger l’acte de naissance.';
                        }

                        if (!this.representantLien) {
                            nextErrors.representant_lien =
                                'Veuillez indiquer le représentant légal.';
                        }

                        if (!this.representantPieceType) {
                            nextErrors.piece_identite_representant_type =
                                'Veuillez choisir le type de pièce du représentant.';
                        }

                        if (!hasFile('piece_identite_representant')) {
                            nextErrors.piece_identite_representant =
                                'Veuillez télécharger la pièce d’identité du représentant.';
                        }
                    }

                    this.fieldErrors = {
                        ...this.fieldErrors,
                        ...nextErrors,
                    };

                    return !Object.values(nextErrors)
                        .some((message) => !!message);
                },

                /* ========================================================
                 * VALIDATION ÉTAPE 3 (INFORMATIONS DU COMPTE)
                 * ======================================================== */

                validateAccount() {
                    this.syncFromForm();

                    const nextErrors = {};

                    if (!this.userType) {
                        nextErrors.user_type =
                            'Veuillez choisir un type de compte.';
                    }

                    /* Téléphone international obligatoire. */
                    if (!this.telephone) {
                        nextErrors.telephone =
                            'Le numéro de téléphone est obligatoire.';
                    } else if (
                        !/^\+[1-9]\d{7,14}$/.test(
                            this.telephone
                        )
                    ) {
                        nextErrors.telephone =
                            'Le numéro de téléphone doit être au format international avec son indicatif pays (ex. +50938123456).';
                    }

                    /* NIF. */
                    if (
                        this.nif &&
                        !/^\d{3}-\d{3}-\d{3}-\d$/.test(
                            this.nif
                        )
                    ) {
                        nextErrors.nif =
                            'Le NIF doit être au format 000-000-000-0.';
                    } else if (
                        this.nif &&
                        this.nifStatus === 'error'
                    ) {
                        nextErrors.nif =
                            this.nifMessage ||
                            'Ce NIF n’est pas disponible.';
                    }

                    /* NINU. */
                    if (
                        this.ninu &&
                        !/^[78]-\d{5}$/.test(
                            this.ninu
                        )
                    ) {
                        nextErrors.ninu =
                            'Le NINU doit être au format 7-12345 ou 8-12345.';
                    }

                    /*
                     * Code pension : désormais OPTIONNEL.
                     * On valide uniquement le format s'il est renseigné.
                     */
                    if (
                        this.userType === 'pensionne' &&
                        this.pension_code
                    ) {
                        if (
                            !/^\d-\d{5}$/.test(
                                this.pension_code
                            )
                        ) {
                            nextErrors.pension_code =
                                'Le code pension doit être au format 0-00000 (ex. 8-34321).';
                        } else if (
                            this.pension_codeStatus ===
                            'error'
                        ) {
                            nextErrors.pension_code =
                                this.pension_codeMessage ||
                                'Ce code pension n’est pas disponible.';
                        }
                    }

                    /* Email. */
                    if (
                        this.emailStatus === 'error'
                    ) {
                        nextErrors.email =
                            this.emailMessage ||
                            'Cette adresse e-mail n’est pas disponible.';
                    }

                    /* Mot de passe. */
                    const passwordField =
                        document.getElementById(
                            'password'
                        );

                    if (
                        !passwordField ||
                        passwordField.value.length < 8
                    ) {
                        nextErrors.password =
                            'Le mot de passe doit contenir au moins 8 caractères.';
                    }

                    /* Adresse actuelle. */
                    if (!this.adresse) {
                        nextErrors.adresse =
                            'L’adresse actuelle est obligatoire.';
                    }

                    this.fieldErrors = {
                        ...this.fieldErrors,
                        ...nextErrors,
                    };

                    return !Object.values(nextErrors)
                        .some((message) => !!message);
                },

                /* ========================================================
                 * VALIDATION ÉTAPE 4 (CONDITIONS)
                 * ======================================================== */

                validateTerms() {
                    const nextErrors = {};

                    if (!this.acceptTerms) {
                        nextErrors.accept_terms =
                            'Vous devez accepter les conditions d’utilisation et la politique de confidentialité.';
                    }

                    this.fieldErrors = {
                        ...this.fieldErrors,
                        ...nextErrors,
                    };

                    return !Object.values(nextErrors)
                        .some((message) => !!message);
                },
            };
        }
    </script>
</x-guest-layout>