/**
 * Wizard demande de creation de compte (/demande-compte).
 */
export function registerDemandeCompteForm(Alpine) {
    Alpine.data('demandeCompteForm', demandeCompteForm);
}
function demandeCompteForm(initial) {
    const i18n = initial.i18n || {};
    const pieceLabels = i18n.piece_labels || {};
    const lienLabels = i18n.lien_labels || {};
    const validation = i18n.validation || {};
    const busyLabels = i18n.busy || {};

    const fieldValue = (id) =>
        document.getElementById(id)?.value?.trim() || '';

    const stashedFiles = initial.stashedFiles || {};

    const hasFile = (id) => {
        if (stashedFiles[id]) {
            return true;
        }

        const field = document.getElementById(id);

        return !!(
            field &&
            field.files &&
            field.files.length > 0
        );
    };

    return {
        ui: initial.ui || {},
        v: validation,

        /* ========================================================
         * Ã‰TAT
         * ======================================================== */

        step: initial.step || 1,

        isMineur: !!initial.isMineur,
        isMandataire: !!initial.isMandataire,
        accountProfile: initial.accountProfile || 'titulaire',

        userType: initial.userType || 'pensionne',

        telephone: initial.telephone || '',
        nif: initial.nif || '',
        pension_code: initial.pensionCode || '',
        adresse: initial.adresse || '',

        pieceType: initial.pieceType || '',
        representantLien: initial.representantLien || '',
        representantPieceType: initial.representantPieceType || '',
        representantEstPensionne: !!initial.representantEstPensionne,
        mandataireEstPensionne: !!initial.mandataireEstPensionne,

        acceptTerms: !!initial.acceptTerms,

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
        _submitSent: false,

        ocrUrl: initial.ocrUrl,
        availabilityUrl: initial.availabilityUrl,

        /* ========================================================
         * BLOCS DÃ‰PLIABLES (Ã‰TAPE 4)
         * ======================================================== */

        openSections: {
            resume: false,
            pieces: false,
            ocr: false,
        },

        closeRecapSections() {
            this.openSections.resume = false;
            this.openSections.pieces = false;
            this.openSections.ocr = false;
        },

        /* ========================================================
         * DISPONIBILITÃ‰
         * ======================================================== */

        nifStatus: '',
        nifMessage: '',

        pension_codeStatus: '',
        pension_codeMessage: '',

        _checkTimers: {
            nif: null,
            pension_code: null,
        },

        _checkSeq: {
            nif: 0,
            pension_code: 0,
        },

        /* ========================================================
         * OCR
         * ======================================================== */

        ocrDocuments: [],
        ocrFields: {},

        previews: {},

        files: {
            piece_identite: stashedFiles.piece_identite || '',
            acte_naissance: stashedFiles.acte_naissance || '',
            piece_identite_representant:
                stashedFiles.piece_identite_representant || '',
            mandat_designation: stashedFiles.mandat_designation || '',
        },

        syncUserTypeFromProfile() {
            const alsoTitulaire =
                (this.isMineur && this.representantEstPensionne) ||
                (this.isMandataire && this.mandataireEstPensionne);

            this.userType =
                (this.isMineur || this.isMandataire) && !alsoTitulaire
                    ? 'externe'
                    : 'pensionne';
        },

        applyAccountProfile() {
            this.isMineur = this.accountProfile === 'mineur';
            this.isMandataire = this.accountProfile === 'mandataire';
            if (!this.isMineur) {
                this.representantEstPensionne = false;
            }
            if (!this.isMandataire) {
                this.mandataireEstPensionne = false;
            }
            this.syncUserTypeFromProfile();
            if (this.isMandataire && this.pension_code) {
                this.scheduleAvailabilityCheck('pension_code');
            }
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

            matricule_fiscal: [
                'nif',
                'nom',
                'prenom',
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
         * Ã‰TAPES
         * ======================================================== */

        steps: i18n.steps || [],

        fieldLabels: i18n.field_labels || {},

        formFieldLabels: i18n.form_field_labels || {},

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
            if (this.userType === 'pensionne') {
                return i18n.user_type_pensionne || this.userType;
            }
            if (this.userType === 'externe') {
                return i18n.user_type_externe || this.userType;
            }

            return this.userType;
        },

        get pieceTypeLabel() {
            return pieceLabels[this.pieceType] || 'â€”';
        },

        get representantLienLabel() {
            return lienLabels[this.representantLien] || 'â€”';
        },

        get representantPieceTypeLabel() {
            return pieceLabels[this.representantPieceType] || 'â€”';
        },

        /* ========================================================
         * RÃ‰CAPITULATIF DES ERREURS
         * ======================================================== */

        get errorSummary() {
            const entries = Object.entries(this.fieldErrors || {})
                .filter(([, message]) => !!message);

            return entries
                .map(([field, message], index) => ({
                    field,
                    message,
                    label: this.formFieldLabels[field] || field.replaceAll('_', ' '),
                    step: this.stepForField(field),
                    index,
                }))
                .sort((a, b) => (a.step - b.step) || (a.index - b.index));
        },

        get errorCount() {
            return this.errorSummary.length;
        },

        /* ========================================================
         * INIT
         * ======================================================== */

        init() {
            if ((initial.ocrDocuments || []).length) {
                this.ocrDocuments = initial.ocrDocuments.map(
                    (doc) => this.hydrateDoc(doc)
                );
                this.syncOcrFields();
            }

            this.prefillAccountFieldsFromOcr();

            if ((this.nif || '').trim()) {
                this.scheduleAvailabilityCheck('nif');
            }

            if (
                (this.userType === 'pensionne' ||
                    this.isMineur ||
                    this.isMandataire) &&
                (this.pension_code || '').trim()
            ) {
                this.scheduleAvailabilityCheck('pension_code');
            }

            /*
             * Synchronise les fichiers prÃ©sents aprÃ¨s un retour
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

        clearAllErrors() {
            this.fieldErrors = {};
            this.stepError = '';
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
         * ROUTAGE DES ERREURS â†’ Ã‰TAPES
         * ======================================================== */

        stepForField(field) {
            const map = {
                is_mineur: 1,
                piece_identite_type: 1,
                piece_identite: 1,
                acte_naissance: 1,
                representant_lien: 1,
                piece_identite_representant_type: 1,
                piece_identite_representant: 1,

                ocr_documents_json: 2,

                user_type: 3,
                telephone: 3,
                nif: 3,
                pension_code: 3,
                password: 3,
                adresse: 3,

                accept_terms: 4,
            };

            return map[field] || this.step;
        },

        jumpToError(field) {
            const target = this.stepForField(field);

            if (target && target !== this.step) {
                this.step = target;
                this.stepError = '';
            }

            // Laisse le temps Ã  x-show de rÃ©afficher le champ
            // avant de repositionner le focus.
            setTimeout(() => {
                const el = document.querySelector(`[name="${field}"]`);

                if (el && typeof el.focus === 'function') {
                    el.focus({ preventScroll: true });
                    el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'center',
                    });
                }
            }, 100);
        },

        /* ========================================================
         * TÃ‰LÃ‰PHONE
         * ======================================================== */

        normalizeTelephone() {
            this.telephone =
                (this.telephone || '').replace(/\s+/g, '');
        },

        /* ========================================================
         * DISPONIBILITÃ‰
         * ======================================================== */

        scheduleAvailabilityCheck(field) {
            clearTimeout(this._checkTimers[field]);

            const value = (this[field] || '').trim();

            this[field + 'Message'] = '';

            if (field === 'nif') {
                if (value === '') {
                    this.nifStatus = '';
                    this.nifMessage = '';
                    return;
                }

                if (!/^\d{3}-\d{3}-\d{3}-\d$/.test(value)) {
                    this.nifStatus = 'error';
                    this.nifMessage = validation.nif_format;
                    return;
                }
            }

            if (field === 'pension_code') {
                /*
                 * Code pension dÃ©sormais optionnel :
                 * s'il est vide, aucune vÃ©rification.
                 * S'il est rempli, on valide juste le format.
                 */
                if (value === '') {
                    this.pension_codeStatus = '';
                    this.pension_codeMessage = '';
                    return;
                }

                if (!/^(7-[A-Z0-9]{5}|8-\d{5})$/i.test(value)) {
                    this.pension_codeStatus = 'error';
                    this.pension_codeMessage = validation.pension_code_format;
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
                        ...(field === 'pension_code' && this.isMandataire
                            ? { is_mandataire: '1' }
                            : {}),
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
                    const url = preview?.url;
                    if (typeof url === 'string' && url.startsWith('blob:')) {
                        URL.revokeObjectURL(url);
                    }
                });

            this.previews = {};
        },

        async loadPdfJs() {
            if (this._pdfJs) {
                return this._pdfJs;
            }

            const pdfjs = await import(
                'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.4.168/build/pdf.min.mjs'
            );

            pdfjs.GlobalWorkerOptions.workerSrc =
                'https://cdn.jsdelivr.net/npm/pdfjs-dist@4.4.168/build/pdf.worker.min.mjs';

            this._pdfJs = pdfjs;

            return pdfjs;
        },

        async renderPdfFirstPage(file) {
            const pdfjs = await this.loadPdfJs();
            const data = new Uint8Array(await file.arrayBuffer());
            const pdf = await pdfjs.getDocument({ data }).promise;
            const page = await pdf.getPage(1);
            const baseViewport = page.getViewport({ scale: 1 });
            const maxWidth = 720;
            const scale = Math.min(2, maxWidth / baseViewport.width);
            const viewport = page.getViewport({ scale });
            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');

            canvas.width = viewport.width;
            canvas.height = viewport.height;

            await page.render({
                canvasContext: context,
                viewport,
            }).promise;

            return {
                url: canvas.toDataURL('image/jpeg', 0.92),
                loading: false,
                fromPdf: true,
            };
        },

        async buildPreviews() {
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

            for (const key of keys) {
                const file =
                    document.getElementById(key)
                        ?.files?.[0];

                if (!file) {
                    continue;
                }

                const isPdf =
                    file.type === 'application/pdf' ||
                    /\.pdf$/i.test(file.name);

                if (file.type.startsWith('image/')) {
                    next[key] = {
                        url: URL.createObjectURL(file),
                        loading: false,
                    };
                    continue;
                }

                if (isPdf) {
                    next[key] = { url: '', loading: true };
                    this.previews = { ...next };

                    try {
                        next[key] = await this.renderPdfFirstPage(file);
                    } catch {
                        next[key] = {
                            url: '',
                            loading: false,
                            error: true,
                        };
                    }

                    this.previews = { ...next };
                }
            }

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

        /**
         * Relie les clÃ©s OCR (CIN, passeport, permis, matricule fiscal)
         * aux champs de lâ€™Ã©tape 3 lorsquâ€™ils existent.
         */
        prefillAccountFieldsFromOcr() {
            this.syncOcrFields();

            const sources = this.ocrFields || {};
            const mappings = [
                {
                    ocrKeys: ['nif'],
                    target: 'nif',
                    format: (value) =>
                        this.formatNifForInput(value),
                },
                {
                    ocrKeys: ['adresse'],
                    target: 'adresse',
                    format: (value) => String(value || '').trim(),
                },
                {
                    ocrKeys: ['telephone'],
                    target: 'telephone',
                    format: (value) =>
                        String(value || '').replace(/\s+/g, ''),
                },
                {
                    ocrKeys: ['code_pension', 'pension_code'],
                    target: 'pension_code',
                    format: (value) =>
                        String(value || '').trim().toUpperCase(),
                },
            ];

            mappings.forEach(({ ocrKeys, target, format }) => {
                const current = String(this[target] ?? '').trim();
                if (current !== '') {
                    return;
                }

                let raw = '';
                for (const key of ocrKeys) {
                    const candidate = String(
                        sources[key] ?? ''
                    ).trim();
                    if (candidate !== '') {
                        raw = candidate;
                        break;
                    }
                }

                if (raw === '') {
                    return;
                }

                const formatted = format(raw);
                if (formatted === '') {
                    return;
                }

                this[target] = formatted;

                const inputId =
                    target === 'pension_code'
                        ? 'pension_code'
                        : target;
                const input =
                    document.getElementById(inputId);
                if (input && input.value !== formatted) {
                    input.value = formatted;
                }
            });

            if ((this.nif || '').trim()) {
                this.scheduleAvailabilityCheck('nif');
            }

            if (
                (this.userType === 'pensionne' ||
                    this.isMineur ||
                    this.isMandataire) &&
                (this.pension_code || '').trim()
            ) {
                this.scheduleAvailabilityCheck('pension_code');
            }

        },

        formatNifForInput(value) {
            const digits = String(value || '')
                .replace(/\D+/g, '')
                .slice(0, 10);

            if (digits.length !== 10) {
                return digits;
            }

            return digits.replace(
                /^(\d{3})(\d{3})(\d{3})(\d)$/,
                '$1-$2-$3-$4'
            );
        },

        async applyOcr(payload) {
            this.ocrDocuments =
                (payload?.documents || [])
                    .map((doc) =>
                        this.hydrateDoc(doc)
                    );

            this.syncOcrFields();
            this.prefillAccountFieldsFromOcr();

            await this.buildPreviews();
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
                validation.generic_error;
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
            this.busyLabel = busyLabels.ocr || '';
            this.stepError = '';

            try {
                const formData = new FormData();

                formData.append(
                    'is_mineur',
                    this.isMineur ? '1' : '0'
                );
                formData.append(
                    'is_mandataire',
                    this.isMandataire ? '1' : '0'
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
                * (tÃ©lÃ©phone, email, nif, ninu, pension_code,
                * password, adresse, accept_terms).
                * Ces informations appartiennent Ã  l'Ã©tape 3.
                */

                const data = await this.postRequest(
                    this.ocrUrl,
                    formData
                );

                await this.applyOcr(data);

                return true;

            } catch (error) {
                /*
                 * Le backend renvoie un 422 contenant malgrÃ© tout
                 * les `documents` OCR extraits (avec un flag
                 * `error` par document). On hydrate donc l'Ã©tat
                 * pour permettre Ã  l'utilisateur de corriger
                 * manuellement les champs, puis on avance quand
                 * mÃªme Ã  l'Ã©tape 2 si au moins un document a pu
                 * Ãªtre rÃ©cupÃ©rÃ©.
                 */
                await this.applyOcr(error);

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
                fieldValue('user_type') || this.userType;

            this.telephone =
                fieldValue('telephone');

            this.nif =
                fieldValue('nif');

            this.pension_code =
                fieldValue('pension_code');

            this.adresse =
                fieldValue('adresse');

            this.isMineur = this.accountProfile === 'mineur';
            this.isMandataire = this.accountProfile === 'mandataire';

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

            const repPensionneInput = document.querySelector(
                '[name="representant_est_pensionne"]'
            );
            if (repPensionneInput) {
                this.representantEstPensionne = repPensionneInput.checked;
            }

            const mandPensionneInput = document.querySelector(
                '[name="mandataire_est_pensionne"]'
            );
            if (mandPensionneInput) {
                this.mandataireEstPensionne = mandPensionneInput.checked;
            }

            this.syncUserTypeFromProfile();
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
             * Ã‰TAPE 1 :
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
             * Ã‰TAPE 2 :
             * donnÃ©es OCR vÃ©rifiÃ©es â†’ informations du compte.
             */
            if (this.step === 2) {
                this.syncOcrFields();
                this.syncFromForm();
                this.prefillAccountFieldsFromOcr();

                this.step = 3;

                return;
            }

            /*
             * Ã‰TAPE 3 :
             * validation des informations â†’ rÃ©capitulatif.
             */
            if (this.step === 3) {
                this.syncFromForm();

                if (!this.validateAccount()) {
                    return;
                }

                this.closeRecapSections();
                this.step = 4;

                return;
            }
        },

        /* ========================================================
         * SOUMISSION
         * ======================================================== */

        isSubmitting() {
            return this.step === 4
                && this.busy
                && (this._submitSent || this._redirecting);
        },

        resetSubmitState() {
            if (this._redirecting) {
                return;
            }

            this.busy = false;
            this.busyLabel = '';
            this._submitSent = false;
        },

        handlePageShow(event) {
            if (this._redirecting) {
                return;
            }

            this.busy = false;
            this.busyLabel = '';
            this._submitSent = false;
        },

        async onSubmit() {
            if (this.busy || this._redirecting || this._submitSent) {
                return;
            }

            const finalStep = this.step === 4;

            if (finalStep) {
                this._submitSent = true;
                this.busy = true;
                this.busyLabel = busyLabels.verifying || '';
                this.stepError = '';
                this._redirecting = false;
            }

            this.syncFromForm();

            if (!this.validateDocuments()) {
                this.step = 1;
                if (finalStep) {
                    this.stepError = validation.documents_step_error;
                    this.resetSubmitState();
                }
                return;
            }

            if (!this.validateAccount()) {
                this.step = 3;
                if (finalStep) {
                    this.stepError = validation.account_step_error;
                    this.resetSubmitState();
                }
                return;
            }

            if (!this.validateTerms()) {
                this.step = 4;
                if (finalStep) {
                    this.stepError = validation.accept_terms_submit;
                    this.resetSubmitState();
                }
                return;
            }

            this.syncOcrFields();

            const form = this.$refs.form;

            /* Normaliser le code pension avant envoi */
            const pensionInput = document.getElementById('pension_code');

            if (pensionInput) {
                const trimmed = pensionInput.value.trim();
                if (trimmed === '') {
                    pensionInput.value = '';
                    this.pension_code = '';
                } else {
                    pensionInput.value = trimmed.toUpperCase();
                    this.pension_code = pensionInput.value;
                }
            }

            const formData = new FormData(form);

            formData.set(
                'ocr_documents_json',
                JSON.stringify(this.ocrDocuments)
            );

            formData.set('idempotency_key', this.idempotencyKey);

            if (! (formData.get('pension_code') || '').toString().trim()) {
                formData.delete('pension_code');
            }

            this._submitSent = true;
            this.busy = true;
            this.busyLabel = busyLabels.submitting || '';
            this.stepError = '';
            this._redirecting = false;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': this.csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-Demande-Compte-Submit': '1',
                    },
                    credentials: 'same-origin',
                    redirect: 'manual',
                    body: formData,
                });

                const rdvFallback = initial.rdvFallbackUrl || '';

                const resolveRedirect = (url) => {
                    const candidate = (url || '').trim() || rdvFallback;
                    try {
                        return new URL(candidate, window.location.origin).href;
                    } catch {
                        return rdvFallback;
                    }
                };

                const goToRdv = (url) => {
                    const target = resolveRedirect(url);
                    this._redirecting = true;
                    this.busyLabel = busyLabels.redirect || '';
                    sessionStorage.removeItem('demande_compte_idem');
                    window.setTimeout(() => {
                        window.location.replace(target);
                    }, 0);
                };

                if (response.status === 419) {
                    this.stepError = validation.session_expired;
                    this.resetSubmitState();
                    return;
                }

                if (response.status === 429) {
                    const data = await response.json().catch(() => ({}));
                    this.stepError = data.message
                        || validation.submission_in_progress;
                    this.resetSubmitState();
                    return;
                }

                /* â”€â”€ Redirection HTTP (rÃ©ponse non-JSON) â”€â”€ */
                if (response.status >= 300 && response.status < 400) {
                    goToRdv(response.headers.get('Location') || rdvFallback);
                    return;
                }

                /* â”€â”€ 422 : validation â”€â”€ */
                if (response.status === 422) {
                    const data = await response.json().catch(() => ({}));
                    const errors = data.errors || {};

                    if (data.message && Object.keys(errors).length === 0) {
                        this.stepError = data.message;
                        this.resetSubmitState();
                        return;
                    }

                    this.applyServerErrors(errors);
                    this.step = this.stepFromServerErrors(errors);
                    this.resetSubmitState();

                    return;
                }

                /* â”€â”€ 2xx : JSON avec redirect_url â”€â”€ */
                if (response.ok) {
                    const raw = await response.text();
                    let data = {};
                    try { data = JSON.parse(raw); } catch { /* HTML or empty */ }

                    if (data.ok === false) {
                        this.stepError = data.message
                            || validation.submit_error;
                        this.resetSubmitState();
                        return;
                    }

                    goToRdv(data.redirect_url || rdvFallback);
                    return;
                }

                /* â”€â”€ Other error â”€â”€ */
                const data = await response.json().catch(() => ({}));
                this.stepError = data.message
                    || validation.submit_error;
                this.resetSubmitState();

            } catch (e) {
                this.stepError = validation.server_unreachable;
                this.resetSubmitState();
            } finally {
                if (!this._redirecting) {
                    this.resetSubmitState();
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
            const known = [
                'is_mineur',
                'piece_identite_type',
                'piece_identite',
                'acte_naissance',
                'representant_lien',
                'piece_identite_representant_type',
                'piece_identite_representant',

                'ocr_documents_json',

                'user_type',
                'telephone',
                'nif',
                'pension_code',
                'password',
                'adresse',

                'accept_terms',
            ];

            const keys = Object.keys(errors || {});
            const match = keys.find((k) => known.includes(k));

            return match ? this.stepForField(match) : this.step;
        },

        /* ========================================================
         * VALIDATION Ã‰TAPE 1
         * ======================================================== */

        validateDocuments() {
            this.syncFromForm();

            const nextErrors = {};

            if (!this.isMineur) {

                if (!this.pieceType) {
                    nextErrors.piece_identite_type =
                        validation.choose_document_type;
                }

                if (!hasFile('piece_identite')) {
                    nextErrors.piece_identite =
                        validation.upload_document;
                }

                if (this.isMandataire && !hasFile('mandat_designation')) {
                    nextErrors.mandat_designation =
                        validation.upload_mandat;
                }

            } else {

                if (!hasFile('acte_naissance')) {
                    nextErrors.acte_naissance =
                        validation.upload_birth_act;
                }

                if (!this.representantLien) {
                    nextErrors.representant_lien =
                        validation.choose_representant;
                }

                if (!this.representantPieceType) {
                    nextErrors.piece_identite_representant_type =
                        validation.choose_representant_piece;
                }

                if (!hasFile('piece_identite_representant')) {
                    nextErrors.piece_identite_representant =
                        validation.upload_representant_piece;
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
         * VALIDATION Ã‰TAPE 3 (INFORMATIONS DU COMPTE)
         * ======================================================== */

        validateAccount() {
            this.syncFromForm();

            const nextErrors = {};

            if (!this.userType) {
                nextErrors.user_type =
                    validation.choose_account_type;
            }

            /* TÃ©lÃ©phone international obligatoire. */
            if (!this.telephone) {
                nextErrors.telephone =
                    validation.telephone_required;
            } else if (
                !/^\+[1-9]\d{7,14}$/.test(
                    this.telephone
                )
            ) {
                nextErrors.telephone =
                    validation.telephone_format;
            }

            /* NIF (obligatoire sauf pensionnÃ© mineur). */
            if (!this.isMineur) {
                if (!this.nif) {
                    nextErrors.nif =
                        validation.nif_required;
                } else if (
                    !/^\d{3}-\d{3}-\d{3}-\d$/.test(
                        this.nif
                    )
                ) {
                    nextErrors.nif =
                        validation.nif_format;
                } else if (this.nifStatus === 'error') {
                    nextErrors.nif =
                        this.nifMessage ||
                        validation.nif_unavailable;
                }
            }

            /*
            * Code pension : dÃ©sormais OPTIONNEL.
            * Format acceptÃ© (casse ignorÃ©e) :
            *   - 7- suivi de 5 caractÃ¨res alphanumÃ©riques (ex. 7-JM183, 7-l0366)
            *   - 8- suivi de 5 chiffres (ex. 8-18698, 8-00500)
            */
            if (this.pension_code) {
                if (
                    !/^(7-[A-Z0-9]{5}|8-\d{5})$/i.test(
                        this.pension_code
                    )
                ) {
                    nextErrors.pension_code =
                        validation.pension_code_format;
                } else if (
                    this.pension_codeStatus === 'error'
                ) {
                    nextErrors.pension_code =
                        this.pension_codeMessage ||
                        (this.isMandataire || this.isMineur
                            ? validation.pension_code_unrecognized
                            : validation.pension_code_unavailable);
                }
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
                    validation.password_min;
            }

            /* Adresse actuelle. */
            if (!this.adresse) {
                nextErrors.adresse =
                    validation.address_required;
            }

            this.fieldErrors = {
                ...this.fieldErrors,
                ...nextErrors,
            };

            return !Object.values(nextErrors)
                .some((message) => !!message);
        },

        /* ========================================================
         * VALIDATION Ã‰TAPE 4 (CONDITIONS)
         * ======================================================== */

        validateTerms() {
            const nextErrors = {};

            if (!this.acceptTerms) {
                nextErrors.accept_terms =
                    validation.accept_terms;
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