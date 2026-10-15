<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatusEnum;
use App\Enums\UserTypeEnum;
use App\Helpers\CodeGeneratorService;
use App\Models\AffiliationPension;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\PensionnaireReference;
use App\Models\User;
use App\Notifications\DemandeCreationCompteSoumise;
use App\Rules\CodePension;
use App\Rules\Nif;
use App\Rules\Ninu;
use App\Rules\PensionnaireReferenceExists;
use App\Rules\Telephone;
use App\Services\Account\ProvisionalAccountProfileService;
use App\Services\AccountCreation\IdentityVerificationService;
use App\Services\Pension\BeneficiaireProvisioningService;
use App\Services\RencontreMotifService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class DemandeCreationCompteController extends Controller
{
    public function __construct(private IdentityVerificationService $identityVerification) {}

    public function create()
    {
        return view('auth.demande-compte');
    }

    /**
     * Endpoint de disponibilité (NIF, code pension, e-mail).
     */
    public function availability(Request $request)
    {
        $field = (string) $request->query('field', '');
        $value = trim((string) $request->query('value', ''));

        if (! in_array($field, ['nif', 'pension_code', 'email'], true)) {
            return response()->json(['available' => true]);
        }

        if ($field === 'nif') {
            return response()->json($this->nifAvailability($value));
        }

        if ($field === 'pension_code') {
            if ($request->boolean('is_mandataire') || $request->boolean('is_mineur')) {
                return response()->json($this->pensionCodeAvailabilityForRepresentedPensionne($value));
            }

            return response()->json($this->pensionCodeAvailability($value));
        }

        if ($value === '') {
            return response()->json([
                'available' => true,
                'empty' => true,
                'message' => null,
            ]);
        }

        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'available' => false,
                'format_ok' => false,
                'message' => 'Saisissez une adresse e-mail valide.',
            ]);
        }

        $available = ! User::emailExists($value);

        return response()->json([
            'available' => $available,
            'format_ok' => true,
            'message' => $available
                ? 'Adresse e-mail disponible.'
                : 'Cette adresse e-mail est déjà utilisée.',
        ]);
    }

    private function nifAvailability(string $value): array
    {
        if ($value === '') {
            return [
                'available' => true,
                'empty' => true,
                'message' => null,
            ];
        }

        if (! preg_match('/^\d{3}-\d{3}-\d{3}-\d$/', $value)) {
            return [
                'available' => false,
                'format_ok' => false,
                'message' => 'Le NIF doit être au format 000-000-000-0.',
            ];
        }

        $available = ! User::nifExists($value)
            && ! DemandeCreationCompte::pendingDigitsMatch(
                'nif',
                User::normalizeDigits($value)
            );

        return [
            'available' => $available,
            'format_ok' => true,
            'message' => $available ? 'NIF disponible.' : 'Ce NIF est déjà utilisé.',
        ];
    }

    private function pensionCodeAvailabilityForRepresentedPensionne(string $value): array
    {
        if ($value === '') {
            return [
                'available' => true,
                'empty' => true,
                'message' => null,
            ];
        }

        $value = strtoupper(trim($value));

        if (! preg_match('/^(7-[A-Z0-9]{5}|8-\d{5})$/', $value)) {
            return [
                'available' => false,
                'format_ok' => false,
                'message' => 'Le code pension doit être au format 7-XXXXX (ex. 7-JM183) ou 8-XXXXX (ex. 8-34321).',
            ];
        }

        $rule = new PensionnaireReferenceExists;
        $failed = false;
        $rule->validate('pension_code', $value, function () use (&$failed) {
            $failed = true;
        });

        return [
            'available' => ! $failed,
            'format_ok' => true,
            'message' => $failed
                ? 'Ce code pension est introuvable dans la base de référence.'
                : 'Code pension du pensionné reconnu.',
        ];
    }

    /**
     * Si la personne est aussi pensionné titulaire, le code saisi doit être celui
     * du pensionné représenté / mandant, pas le sien (concordance ref. ↔ pièce du mandataire/représentant).
     */
    private function assertPensionCodeIsNotProxyOwnTitulaireCode(
        Request $request,
        string $code,
        bool $isMineur,
        bool $isMandataire,
        \Closure $fail,
    ): void {
        $alsoTitulaire = ($isMineur && $request->boolean('representant_est_pensionne'))
            || ($isMandataire && $request->boolean('mandataire_est_pensionne'));

        if (! $alsoTitulaire) {
            return;
        }

        $ref = PensionnaireReference::query()
            ->whereRaw('UPPER(TRIM(PENSIONNAIRE_ID)) = ?', [strtoupper(trim($code))])
            ->first();

        if (! $ref) {
            return;
        }

        $raw = $request->input('ocr_documents_json');
        $ocrDocuments = [];

        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $ocrDocuments = $decoded;
            }
        }

        $provisioning = app(BeneficiaireProvisioningService::class);
        $matcher = new PensionnaireReferenceExists;

        $proxyKey = $isMineur ? 'piece_identite_representant' : 'piece_identite';
        $proxyFields = $provisioning->fieldsFromDocument($ocrDocuments, $proxyKey);

        if (! $matcher->rowMatchesIdentity(
            $ref,
            $proxyFields['nom'] ?? null,
            $proxyFields['prenom'] ?? null,
        )) {
            return;
        }

        if ($isMineur) {
            $minorFields = $provisioning->fieldsFromDocument($ocrDocuments, 'acte_naissance');
            if ($matcher->rowMatchesIdentity(
                $ref,
                $minorFields['nom'] ?? null,
                $minorFields['prenom'] ?? null,
            )) {
                return;
            }
        }

        $fail(
            'Indiquez le code pension du pensionné concerné (mineur ou personne mandante), '
            .'pas votre code de titulaire.'
        );
    }

    private function pensionCodeAvailability(string $value): array
    {
        if ($value === '') {
            return [
                'available' => true,
                'empty' => true,
                'message' => null,
            ];
        }

        // ✅ Normalisation : on travaille sur la version uppercase
        $value = strtoupper(trim($value));

        if (! preg_match('/^(7-[A-Z0-9]{5}|8-\d{5})$/', $value)) {
            return [
                'available' => true,
                'format_ok' => false,
                'message' => 'Le code pension doit être au format 7-XXXXX (ex. 7-JM183) ou 8-XXXXX (ex. 8-34321).',
            ];
        }

        $available = ! AffiliationPension::codeExists($value)
            && ! DemandeCreationCompte::pendingDigitsMatch(
                'pension_code',
                User::normalizeDigits($value)
            );

        return [
            'available' => $available,
            'format_ok' => true,
            'message' => $available
                ? 'Code pension disponible.'
                : 'Ce code pension est déjà utilisé.',
        ];
    }

    /**
     * Endpoint OCR appelé depuis l'étape 1 → étape 2.
     */
    public function extractOcr(Request $request)
    {
        $isMineur = $request->boolean('is_mineur');

        $request->validate(
            [
                'is_mineur' => ['nullable', 'boolean'],

                'piece_identite_type' => [
                    $isMineur ? 'nullable' : 'required',
                    'nullable',
                    'in:permis,passeport,cin,matricule_fiscal',
                ],

                'piece_identite' => [
                    $isMineur ? 'nullable' : 'required',
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf',
                    'max:5120',
                ],

                'acte_naissance' => [
                    $isMineur ? 'required' : 'nullable',
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf',
                    'max:5120',
                ],

                'representant_lien' => [
                    $isMineur ? 'required' : 'nullable',
                    'nullable',
                    'in:pere,mere,tuteur',
                ],

                'piece_identite_representant_type' => [
                    $isMineur ? 'required' : 'nullable',
                    'nullable',
                    'in:permis,passeport,cin',
                ],

                'piece_identite_representant' => [
                    $isMineur ? 'required' : 'nullable',
                    'nullable',
                    'file',
                    'mimes:jpg,jpeg,png,webp,pdf',
                    'max:5120',
                ],
            ],
            [
                'required' => 'Le champ « :attribute » est obligatoire.',
                'mimes' => 'Le fichier « :attribute » doit être une image (JPG, PNG, WEBP) ou un PDF.',
                'in' => 'La valeur sélectionnée pour « :attribute » est invalide.',
            ],
            [
                'piece_identite_type' => 'type de pièce d’identité',
                'piece_identite' => 'pièce d’identité du pensionné',
                'acte_naissance' => 'acte de naissance du pensionné mineur',
                'representant_lien' => 'lien du représentant',
                'piece_identite_representant_type' => 'type de pièce d’identité du représentant',
                'piece_identite_representant' => 'pièce d’identité du représentant',
            ]
        );

        $files = $isMineur
            ? [
                [
                    'key' => 'acte_naissance',
                    'label' => 'Acte de naissance',
                    'file' => $request->file('acte_naissance'),
                ],
                [
                    'key' => 'piece_identite_representant',
                    'label' => 'Pièce d’identité du représentant',
                    'file' => $request->file('piece_identite_representant'),
                ],
            ]
            : [
                [
                    'key' => 'piece_identite',
                    'label' => 'Pièce d’identité',
                    'file' => $request->file('piece_identite'),
                ],
            ];

        $documents = [];
        $merged = [];
        $childFields = [];

        foreach ($files as $item) {
            if (! $item['file']) {
                continue;
            }

            $declaredType = match ($item['key']) {
                'piece_identite' => $request->input('piece_identite_type'),
                'piece_identite_representant' => $request->input('piece_identite_representant_type'),
                'acte_naissance' => 'acte_naissance',
                default => null,
            };

            $extracted = $this->identityVerification->extract(
                $item['file'],
                is_string($declaredType) ? $declaredType : null,
            );

            $documents[] = [
                'key' => $item['key'],
                'label' => $item['label'],
                'type_document' => $extracted['fields']['type_document'] ?? $declaredType,
                'fields' => $extracted['fields'],
                'error' => (bool) ($extracted['error'] ?? false),
            ];

            $merged = array_merge($merged, $extracted['fields']);

            if ($item['key'] === 'acte_naissance') {
                $childFields = $extracted['fields'];
            }
        }

        foreach (['nom', 'prenom', 'date_naissance', 'sexe', 'lieu_naissance'] as $childKey) {
            if (! empty($childFields[$childKey])) {
                $merged[$childKey] = $childFields[$childKey];
            }
        }

        $ok = $documents !== []
            && collect($documents)->every(fn (array $doc): bool => ! ($doc['error'] ?? false));

        return response()->json(
            [
                'ok' => $ok,
                'documents' => $documents,
                'fields' => $merged,
                'message' => $ok
                    ? null
                    : 'La lecture OCR d’un document a échoué. Vérifiez la qualité du fichier, puis réessayez.',
            ],
            $ok ? 200 : 422
        );
    }

    public function store(Request $request)
    {
        /* ── Validation ── */
        try {
            $validated = $this->validateDemande($request, requireTerms: true);
        } catch (ValidationException $e) {
            if ($this->requestsJsonResponse($request)) {
                return response()->json([
                    'message' => 'Certains champs sont invalides.',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $this->resolveDemandeUserType($request, $validated);

        if (filled($validated['pension_code'] ?? null)) {
            $validated['pension_code'] = strtoupper(trim((string) $validated['pension_code']));
        } else {
            $validated['pension_code'] = null;
        }

        /* ============================================================
         * LAYER 1 — Idempotency key (refresh / retry / double-submit)
         * ============================================================ */
        $idempotencyKey = trim((string) $request->input('idempotency_key', ''));

        if ($this->requestsJsonResponse($request) && $idempotencyKey === '') {
            return response()->json([
                'message' => 'Rechargez la page, puis soumettez à nouveau.',
                'errors' => [
                    'idempotency_key' => ['Rechargez la page, puis soumettez à nouveau.'],
                ],
            ], 422);
        }

        $storeLock = null;

        if ($idempotencyKey !== '') {
            $existing = DemandeCreationCompte::where('idempotency_key', $idempotencyKey)->first();

            if ($existing) {
                return $this->redirectToRdv($request, $existing, 'idempotency_hit');
            }

            $storeLock = Cache::lock('demande_compte:store:'.$idempotencyKey, 120);

            if (! $storeLock->get()) {
                usleep(250_000);
                $existing = DemandeCreationCompte::where('idempotency_key', $idempotencyKey)->first();

                if ($existing) {
                    return $this->redirectToRdv($request, $existing, 'idempotency_hit');
                }

                if ($this->requestsJsonResponse($request)) {
                    return response()->json([
                        'message' => 'Une soumission est déjà en cours. Patientez quelques secondes.',
                    ], 429);
                }

                return back()->with('error', 'Une soumission est déjà en cours. Patientez quelques secondes.');
            }
        }

        try {
            return $this->persistDemandeCreationCompte(
                $request,
                $validated,
                $idempotencyKey !== '' ? $idempotencyKey : null
            );
        } finally {
            $storeLock?->release();
        }
    }

    private function persistDemandeCreationCompte(
        Request $request,
        array $validated,
        ?string $idempotencyKey
    ): Response|RedirectResponse|JsonResponse {

        /* ============================================================
         * LAYER 2 — Field dedup (same person, new session, same data)
         * ============================================================ */
        $existing = $this->findExistingDemande($validated);

        if ($existing) {
            if (filled($idempotencyKey) && blank($existing->idempotency_key)) {
                $existing->update(['idempotency_key' => $idempotencyKey]);
            }

            return $this->redirectToRdv($request, $existing, 'field_duplicate');
        }

        $isMandataire = $request->boolean('is_mandataire');
        $isMineur = $request->boolean('is_mineur') && ! $isMandataire;
        $ocrDocuments = $this->resolveOcrDocuments($request, $isMineur, $validated);
        $ocrFields = $this->flattenOcrFields($ocrDocuments);
        $mismatches = $this->hasOcrError($ocrDocuments) ? ['ocr'] : [];

        $declaredType = $isMineur
            ? ($validated['piece_identite_representant_type'] ?? null)
            : ($validated['piece_identite_type'] ?? null);

        /* ============================================================
         * LAYER 3 — Create + race recovery
         *
         * À la soumission : compte provisoire (users.account_status = provisoire)
         * + demande en_attente liée par user_id, dans une seule transaction.
         * Mot de passe : choisi par l'usager sur le formulaire (jamais généré
         * aléatoirement puis communiqué). Connexion automatique → prise de RDV.
         * ============================================================ */
        try {
            $demande = DB::transaction(function () use (
                $request, $validated, $isMineur, $isMandataire, $declaredType,
                $ocrFields, $ocrDocuments, $mismatches, $idempotencyKey
            ) {
                $provisioning = app(BeneficiaireProvisioningService::class);
                $accountIdentity = $isMineur
                    ? $provisioning->fieldsFromDocument($ocrDocuments, 'piece_identite_representant')
                    : $ocrFields;

                $user = $this->createProvisionalUser(
                    $validated,
                    $accountIdentity,
                    $isMineur,
                    $isMineur && $request->boolean('representant_est_pensionne'),
                    $isMandataire,
                    $isMandataire && $request->boolean('mandataire_est_pensionne'),
                );

                $firstname = $ocrFields['prenom'] ?? null;
                $lastname = $ocrFields['nom'] ?? null;
                $name = trim(implode(' ', array_filter([$firstname, $lastname])));

                $payload = collect($validated)
                    ->except([
                        'password',
                        'accept_terms',
                        'piece_identite',
                        'acte_naissance',
                        'piece_identite_representant',
                        'mandat_designation',
                        'ocr_documents_json',
                        'idempotency_key',
                    ])
                    ->all();

                $payload['ocr_fields'] = $ocrFields;
                $payload['ocr_documents'] = $ocrDocuments;
                $payload['username'] = $user->username;
                $payload['name'] = $name !== '' ? $name : $user->name;

                $payload['files'] = [
                    'piece_identite' => $request->file('piece_identite')?->getClientOriginalName(),
                    'matricule_fiscal' => ($declaredType ?? null) === 'matricule_fiscal'
                        ? $request->file('piece_identite')?->getClientOriginalName()
                        : null,
                    'acte_naissance' => $request->file('acte_naissance')?->getClientOriginalName(),
                    'piece_identite_representant' => $request->file('piece_identite_representant')?->getClientOriginalName(),
                    'mandat_designation' => $request->file('mandat_designation')?->getClientOriginalName(),
                ];

                $pensionCode = filled($validated['pension_code'] ?? null)
                    ? strtoupper(trim((string) $validated['pension_code']))
                    : null;

                $demande = DemandeCreationCompte::create([
                    'code' => CodeGeneratorService::generateUniqueRequestCode(
                        'DEMANDE_CREATION_COMPTE',
                        (new DemandeCreationCompte)->getTable()
                    ),

                    'idempotency_key' => $idempotencyKey,

                    'user_type' => $validated['user_type'] ?? UserTypeEnum::PENSIONNE->value,

                    'email' => filled($validated['email'] ?? null)
                        ? mb_strtolower(trim($validated['email']))
                        : null,

                    'username' => $user->username,
                    'name' => $name !== '' ? $name : $user->name,

                    'nif' => filled($validated['nif'] ?? null) ? $validated['nif'] : null,
                    'ninu' => filled($validated['ninu'] ?? null) ? trim($validated['ninu']) : null,

                    'pension_code' => $pensionCode,

                    'firstname' => $firstname,
                    'lastname' => $lastname,

                    'telephone' => $validated['telephone'],
                    'adresse' => $validated['adresse'],

                    'is_mineur' => $isMineur,
                    'is_mandataire' => $isMandataire,

                    'mandataire_est_pensionne' => $isMandataire
                        && $request->boolean('mandataire_est_pensionne'),

                    'representant_lien' => $isMineur
                        ? ($validated['representant_lien'] ?? null)
                        : null,

                    'representant_est_pensionne' => $isMineur
                        && $request->boolean('representant_est_pensionne'),

                    'piece_identite_representant_type' => $isMineur
                        ? ($validated['piece_identite_representant_type'] ?? null)
                        : null,

                    'pieces_identite' => $declaredType ? [$declaredType] : [],

                    'ocr_fields' => $ocrFields,
                    'ocr_documents' => $ocrDocuments,
                    'verification_mismatches' => $mismatches,
                    'submitted_payload' => $payload,

                    'accepted_terms_at' => now(),
                    'status' => DemandeCreationCompte::STATUS_EN_ATTENTE,
                    'soumise_at' => now(),
                    'user_id' => $user->id,
                ]);

                $this->attachIdentityDocuments($request, $demande, $isMineur, $declaredType);

                if ($isMandataire && $request->hasFile('mandat_designation')) {
                    $demande
                        ->addMediaFromRequest('mandat_designation')
                        ->toMediaCollection('mandat_designation');
                }

                $demande = $demande->fresh();

                $demande->recordHistory(
                    DemandeCreationCompteHistory::EVENT_SOUMISE,
                    "Demande de création de compte soumise ({$demande->code}).",
                    $user,
                    DemandeCreationCompte::STATUS_EN_ATTENTE,
                    [
                        'username' => $user->username,
                        'email' => $user->email,
                    ]
                );

                $demande->recordHistory(
                    DemandeCreationCompteHistory::EVENT_COMPTE_CREE,
                    'Compte provisoire créé : accès limité à la prise de rendez-vous.',
                    $user,
                    DemandeCreationCompte::STATUS_EN_ATTENTE,
                    [
                        'user_id' => $user->id,
                        'account_status' => AccountStatusEnum::PROVISOIRE,
                    ]
                );

                if ($isMandataire) {
                    $provisioning->provisionMandataireFromDemandeCreation(
                        $user,
                        $demande,
                    );
                } else {
                    $provisioning->provisionFromDemandeCreation(
                        $user,
                        $demande,
                        $ocrDocuments,
                        $isMineur
                    );
                }

                return $demande;
            });
        } catch (UniqueConstraintViolationException $e) {
            /* Race: another request won. Find its row. */
            $demande = null;

            if (filled($idempotencyKey)) {
                $demande = DemandeCreationCompte::where('idempotency_key', $idempotencyKey)->first();
            }

            $demande ??= $this->findExistingDemande($validated);

            if (! $demande) {
                throw $e;
            }

            return $this->redirectToRdv($request, $demande, 'race_recovered');
        }

        /* ── Notify ONLY on genuine first creation ── */
        if ($demande->user) {
            try {
                $demande->user->notify(new DemandeCreationCompteSoumise($demande));
            } catch (\Throwable $e) {
                Log::warning('Notification DemandeCreationCompteSoumise non envoyée', [
                    'demande_id' => $demande->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $this->redirectToRdv($request, $demande, 'created');
    }

    private function requestsJsonResponse(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->wantsJson()
            || $request->filled('idempotency_key');
    }

    /**
     * Look for an existing "live" demande matching identifying fields.
     * Strongest identifiers first.
     */
    private function findExistingDemande(array $validated): ?DemandeCreationCompte
    {
        $candidates = array_filter([
            'nif' => $validated['nif'] ?? null,
            'ninu' => $validated['ninu'] ?? null,
            'pension_code' => $validated['pension_code'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
            'email' => $validated['email'] ?? null,
        ], fn ($v) => filled($v));

        if ($candidates === []) {
            return null;
        }

        return DemandeCreationCompte::query()
            ->where(function ($q) use ($candidates) {
                foreach ($candidates as $field => $value) {
                    $q->orWhere($field, $value);
                }
            })
            ->where('status', DemandeCreationCompte::STATUS_EN_ATTENTE)
            ->whereNotNull('user_id')
            ->latest('id')
            ->first();
    }

    /**
     * Single exit point for the RDV redirect — used for both first create
     * and every duplicate path. Always 200 for AJAX.
     */
    private function redirectToRdv(
        Request $request,
        DemandeCreationCompte $demande,
        string $reason
    ): Response|RedirectResponse|JsonResponse {
        if (! $demande->user) {
            $message = 'Votre demande a été enregistrée, mais la prise de rendez-vous en ligne '
                .'n’est pas disponible pour ce dossier. Veuillez contacter la Direction.';

            if ($this->requestsJsonResponse($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => $message,
                    'demande_id' => $demande->id,
                ], 422);
            }

            return redirect()
                ->route('demandes.compte.create')
                ->with('error', $message);
        }

        $user = $demande->user->fresh(['demandeCreationCompte']);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->save();

        $url = $this->postSubmitRedirectUrl($user);

        $rdvCreateUrl = route('demandes.rencontre.create', absolute: true);

        if (! str_starts_with($url, $rdvCreateUrl)) {
            $request->session()->flash(
                'success',
                'Votre demande a été enregistrée. Prenez rendez-vous pour finaliser l’ouverture de votre compte.'
            );
        }

        if ($this->requestsJsonResponse($request)) {
            return response()->json([
                'ok' => true,
                'already_exists' => $reason !== 'created',
                'reason' => $reason,
                'demande_id' => $demande->id,
                'redirect_url' => $url,
            ], 200);
        }

        return redirect()->to($url);
    }

    private function postSubmitRedirectUrl(User $user): string
    {
        $motifs = app(RencontreMotifService::class);

        if ($user->hasPensionPortalAccess()
            || $motifs->userHasPendingDemandeCreationCompte($user)) {
            $params = [];

            if ($motifs->userHasPendingDemandeCreationCompte($user)) {
                $params['motif'] = 'demande_formalites_mandats';
            }

            return route('demandes.rencontre.create', $params, absolute: true);
        }

        return route('compte.provisoire', absolute: true);
    }

    private function resolveOcrDocuments(
        Request $request,
        bool $isMineur,
        array $validated
    ): array {
        $raw = $request->input('ocr_documents_json');

        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);

            if (is_array($decoded) && $decoded !== []) {
                return array_values(
                    array_filter(
                        array_map(
                            fn ($doc) => $this->normalizeOcrDocument($doc),
                            $decoded
                        )
                    )
                );
            }
        }

        $isMineur = $isMineur || $request->boolean('is_mineur');

        $documents = [];

        if ($isMineur) {
            $sources = [
                [
                    'key' => 'acte_naissance',
                    'label' => 'Acte de naissance',
                    'input' => 'acte_naissance',
                    'declared' => 'acte_naissance',
                ],
                [
                    'key' => 'piece_identite_representant',
                    'label' => 'Pièce du représentant',
                    'input' => 'piece_identite_representant',
                    'declared' => $validated['piece_identite_representant_type'] ?? null,
                ],
            ];
        } else {
            $sources = [
                [
                    'key' => 'piece_identite',
                    'label' => 'Pièce d’identité',
                    'input' => 'piece_identite',
                    'declared' => $validated['piece_identite_type'] ?? null,
                ],
            ];
        }

        foreach ($sources as $source) {
            if (! $request->hasFile($source['input'])) {
                continue;
            }

            $extracted = $this->identityVerification->extract(
                $request->file($source['input']),
                is_string($source['declared']) ? $source['declared'] : null,
            );

            $documents[] = [
                'key' => $source['key'],
                'label' => $source['label'],
                'type_document' => $extracted['fields']['type_document'] ?? $source['declared'],
                'error' => (bool) ($extracted['error'] ?? false),
                'fields' => is_array($extracted['fields'] ?? null) ? $extracted['fields'] : [],
            ];
        }

        return $documents;
    }

    private function normalizeOcrDocument($doc): array
    {
        $doc = is_array($doc) ? $doc : [];

        $fields = [];

        foreach ((array) ($doc['fields'] ?? []) as $k => $v) {
            $key = is_string($k) ? trim($k) : '';
            $val = is_scalar($v) ? trim((string) $v) : '';

            if ($key !== '' && $val !== '') {
                $fields[$key] = $val;
            }
        }

        return [
            'key' => isset($doc['key']) ? (string) $doc['key'] : '',
            'label' => isset($doc['label']) ? (string) $doc['label'] : '',
            'type_document' => isset($doc['type_document']) ? (string) $doc['type_document'] : null,
            'error' => (bool) ($doc['error'] ?? false),
            'fields' => $fields,
        ];
    }

    private function flattenOcrFields(array $documents): array
    {
        $order = [
            'piece_identite' => 1,
            'piece_identite_representant' => 2,
            'acte_naissance' => 3,
        ];

        usort(
            $documents,
            function ($a, $b) use ($order) {
                return ($order[$a['key'] ?? ''] ?? 0) <=> ($order[$b['key'] ?? ''] ?? 0);
            }
        );

        $flat = [];

        foreach ($documents as $doc) {
            foreach (($doc['fields'] ?? []) as $k => $v) {
                if ($k === 'type_document') {
                    continue;
                }

                $val = trim((string) $v);

                if ($val !== '') {
                    $flat[$k] = $val;
                }
            }
        }

        return $flat;
    }

    private function hasOcrError(array $documents): bool
    {
        foreach ($documents as $doc) {
            if (! empty($doc['error'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validation complète de la demande.
     */
    private function validateDemande(
        Request $request,
        bool $requireTerms
    ): array {
        $isMandataire = $request->boolean('is_mandataire');
        $isMineur = $request->boolean('is_mineur') && ! $isMandataire;

        $fileRule = 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';

        $rules = [
            'idempotency_key' => ['nullable', 'string', 'max:64'],
            'user_type' => 'required|in:pensionne,externe',

            'is_mandataire' => [
                'sometimes',
                'boolean',
            ],

            'nif' => array_merge(
                $isMineur ? ['nullable'] : ['required'],
                [
                    'string',
                    new Nif,
                    function (string $attribute, mixed $value, \Closure $fail) {
                        if (blank($value)) {
                            return;
                        }

                        if (
                            User::nifExists($value) ||
                            DemandeCreationCompte::pendingDigitsMatch(
                                'nif',
                                User::normalizeDigits($value)
                            )
                        ) {
                            $fail('Ce NIF est déjà utilisé.');
                        }
                    },
                ]
            ),

            'ninu' => [
                'nullable',
                'string',
                new Ninu,
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        User::ninuExists($value) ||
                        DemandeCreationCompte::pendingDigitsMatch(
                            'ninu',
                            User::normalizeDigits($value)
                        )
                    ) {
                        $fail('Ce NINU est déjà utilisé.');
                    }
                },
            ],

            'pension_code' => array_merge(
                ['nullable'],
                [
                    'string',
                    new CodePension,
                    new PensionnaireReferenceExists,
                    function (string $attribute, mixed $value, \Closure $fail) use ($isMandataire, $isMineur, $request) {
                        if (blank($value)) {
                            return;
                        }

                        // Représentant / mandataire : code du pensionné concerné (pas « déjà pris » comme pour un titulaire).
                        if ($isMandataire || $isMineur) {
                            $this->assertPensionCodeIsNotProxyOwnTitulaireCode(
                                $request,
                                (string) $value,
                                $isMineur,
                                $isMandataire,
                                $fail
                            );

                            return;
                        }

                        if (
                            AffiliationPension::codeExists($value) ||
                            DemandeCreationCompte::pendingDigitsMatch(
                                'pension_code',
                                User::normalizeDigits($value)
                            )
                        ) {
                            $fail('Ce code pension est déjà utilisé.');
                        }
                    },
                ]
            ),

            'telephone' => [
                'required',
                'string',
                new Telephone,
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        User::telephoneExists($value) ||
                        DemandeCreationCompte::pendingTelephoneExists($value)
                    ) {
                        $fail('Ce numéro de téléphone est déjà utilisé.');
                    }
                },
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        filled($value) &&
                        is_string($value) &&
                        User::emailExists($value)
                    ) {
                        $fail('Cette adresse e-mail est déjà utilisée.');
                    }
                },
            ],

            'adresse' => [
                'required',
                'filled',
                'string',
                'max:500',
            ],

            'is_mineur' => [
                'sometimes',
                'boolean',
            ],

            'piece_identite_type' => $isMineur
                ? 'nullable'
                : 'required|in:permis,passeport,cin,matricule_fiscal',

            'piece_identite' => $isMineur
                ? 'nullable'
                : $fileRule,

            'acte_naissance' => $isMineur
                ? $fileRule
                : 'nullable',

            'representant_lien' => $isMineur
                ? 'required|in:pere,mere,tuteur'
                : 'nullable',

            'representant_est_pensionne' => $isMineur
                ? 'sometimes|boolean'
                : 'nullable',

            'piece_identite_representant_type' => $isMineur
                ? 'required|in:permis,passeport,cin'
                : 'nullable',

            'piece_identite_representant' => $isMineur
                ? $fileRule
                : 'nullable',

            'mandat_designation' => $isMandataire
                ? $fileRule
                : 'nullable',

            'mandataire_est_pensionne' => $isMandataire
                ? 'sometimes|boolean'
                : 'nullable',

            'ocr_documents_json' => [
                'nullable',
                'string',
                'max:524288',
            ],
        ];

        if ($requireTerms) {
            $rules['password'] = [
                'required',
                'string',
                'min:8',
                'max:72',
                Password::defaults(),
            ];

            $rules['accept_terms'] = 'accepted';
        }

        return $request->validate(
            $rules,
            [
                'required' => 'Le champ « :attribute » est obligatoire.',
                'accepted' => 'Vous devez accepter les :attribute.',
                'mimes' => 'Le fichier « :attribute » doit être une image (JPG, PNG, WEBP) ou un PDF.',
                'email' => 'Le champ « :attribute » doit être une adresse e-mail valide.',
                'min' => 'Le champ « :attribute » doit contenir au moins :min caractères.',
            ],
            [
                'user_type' => 'type de compte',
                'nif' => 'matricule fiscal (NIF)',
                'ninu' => 'NINU',
                'pension_code' => 'code pension',
                'telephone' => 'numéro de téléphone',
                'email' => 'adresse e-mail',
                'adresse' => 'adresse actuelle',
                'is_mineur' => 'pensionné mineur',
                'piece_identite_type' => 'type de pièce d’identité',
                'piece_identite' => 'pièce d’identité du pensionné',
                'acte_naissance' => 'acte de naissance du pensionné mineur',
                'representant_lien' => 'lien du représentant',
                'piece_identite_representant_type' => 'type de pièce d’identité du représentant',
                'piece_identite_representant' => 'pièce d’identité du représentant',
                'mandat_designation' => 'formulaire de désignation du mandataire',
                'password' => 'mot de passe',
                'accept_terms' => 'conditions d’utilisation et politique de confidentialité',
            ]
        );
    }

    /**
     * Représentant / mandataire : « externe » sauf si la personne est aussi pensionné titulaire.
     *
     * @param  array<string, mixed>  $validated
     */
    private function resolveDemandeUserType(Request $request, array &$validated): void
    {
        $isMandataire = $request->boolean('is_mandataire');
        $isMineur = $request->boolean('is_mineur') && ! $isMandataire;
        $alsoTitulaire = ($isMineur && $request->boolean('representant_est_pensionne'))
            || ($isMandataire && $request->boolean('mandataire_est_pensionne'));

        $validated['user_type'] = ($isMineur || $isMandataire) && ! $alsoTitulaire
            ? UserTypeEnum::EXTERNE->value
            : UserTypeEnum::PENSIONNE->value;
    }

    private function createProvisionalUser(
        array $validated,
        array $identityFields,
        bool $isMineur = false,
        bool $representantEstPensionne = false,
        bool $isMandataire = false,
        bool $mandataireEstPensionne = false,
    ): User {
        /*
        |--------------------------------------------------------------------------
        | Email
        |--------------------------------------------------------------------------
        | L'email est facultatif.
        | S'il n'est pas fourni, on enregistre NULL.
        */
        $email = filled($validated['email'] ?? null)
            ? mb_strtolower(trim((string) $validated['email']))
            : null;

        /*
        |--------------------------------------------------------------------------
        | Identité
        |--------------------------------------------------------------------------
        | Le prénom et le nom proviennent prioritairement des données OCR.
        */
        $firstname = trim((string) ($identityFields['prenom'] ?? ''));
        $lastname = trim((string) ($identityFields['nom'] ?? ''));

        /*
        |--------------------------------------------------------------------------
        | Nom complet
        |--------------------------------------------------------------------------
        */
        $name = trim(
            implode(
                ' ',
                array_filter([
                    $firstname,
                    $lastname,
                ])
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Username
        |--------------------------------------------------------------------------
        | Le username est basé uniquement sur :
        |
        |     prénom.nom
        |
        | Exemple :
        |     Jean Pierre -> jean.pierre
        |
        | En cas de doublon :
        |     jean.pierre
        |     jean.pierre2
        |     jean.pierre3
        |     ...
        */
        $usernameSource = trim(
            $firstname.'.'.$lastname
        );

        /*
        | Si le prénom et le nom sont disponibles, on utilise
        | prénom.nom.
        |
        | Le fallback "pensionne" évite une chaîne vide dans
        | le cas exceptionnel où l'OCR n'aurait fourni ni prénom
        | ni nom.
        */
        if ($firstname !== '' || $lastname !== '') {
            $username = User::uniqueUsernameFrom($usernameSource);
        } else {
            $username = User::uniqueUsernameFrom('pensionne');
        }

        /*
        |--------------------------------------------------------------------------
        | Création du compte provisoire
        |--------------------------------------------------------------------------
        | Le mot de passe vient du formulaire ($validated['password']), hashé
        | via le cast User. Alternative possible : Hash::make(Str::random(64))
        | + lien « définir mon mot de passe » — non utilisée ici.
        */
        $user = User::create([
            'name' => $name !== '' ? $name : $username,

            'firstname' => $firstname !== ''
                ? $firstname
                : null,

            'lastname' => $lastname !== ''
                ? $lastname
                : null,

            'email' => $email,

            'username' => $username,

            'phone' => $validated['telephone'],

            'password' => $validated['password'],

            'nif' => $isMineur ? null : ($validated['nif'] ?? null),

            'account_status' => AccountStatusEnum::PROVISOIRE,
        ]);

        app(ProvisionalAccountProfileService::class)
            ->configure($user, $isMineur, $representantEstPensionne, $isMandataire, $mandataireEstPensionne);

        return $user;
    }

    private function attachIdentityDocuments(
        Request $request,
        DemandeCreationCompte $demande,
        bool $isMineur,
        ?string $type
    ): void {
        $collections = [
            'permis' => $isMineur ? 'identite_representant_permis' : 'identite_permis',
            'passeport' => $isMineur ? 'identite_representant_passeport' : 'identite_passeport',
            'cin' => $isMineur ? 'identite_representant_cin' : 'identite_cin',
        ];

        $input = $isMineur ? 'piece_identite_representant' : 'piece_identite';

        if (! $isMineur && $type === 'matricule_fiscal' && $request->hasFile($input)) {
            $demande
                ->addMediaFromRequest($input)
                ->toMediaCollection('matricule_fiscal');

            return;
        }

        if ($type && isset($collections[$type]) && $request->hasFile($input)) {
            $demande
                ->addMediaFromRequest($input)
                ->toMediaCollection($collections[$type]);
        }

        if ($isMineur && $request->hasFile('acte_naissance')) {
            $demande
                ->addMediaFromRequest('acte_naissance')
                ->toMediaCollection('acte_naissance');
        }
    }
}
