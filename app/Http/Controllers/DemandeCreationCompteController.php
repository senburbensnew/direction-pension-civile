<?php

namespace App\Http\Controllers;

use App\Enums\UserTypeEnum;
use App\Helpers\CodeGeneratorService;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeCreationCompteHistory;
use App\Models\User;
use App\Models\UserType;
use App\Notifications\DemandeCreationCompteSoumise;
use App\Rules\CodePension;
use App\Rules\Nif;
use App\Rules\Ninu;
use App\Rules\Telephone;
use App\Services\AccountCreation\IdentityVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Rules\PensionnaireReferenceExists;

class DemandeCreationCompteController extends Controller
{
    public function __construct(
        private IdentityVerificationService $identityVerification
    ) {
    }

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
                    'in:permis,passeport,cin',
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
            && collect($documents)->every(fn (array $doc) => ! $doc['error']);

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
        Log::info('Demande store payload', [
            'request' => $request->except(['password']),
            'files'   => $request->allFiles(),
        ]);
    
        /* ── Validation ── */
        try {
            $validated = $this->validateDemande($request, requireTerms: true);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Certains champs sont invalides.',
                    'errors'  => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        if (filled($validated['pension_code'] ?? null)) {
            $validated['pension_code'] = strtoupper(trim((string) $validated['pension_code']));
        } else {
            $validated['pension_code'] = null;
        }
    
        /* ============================================================
         * LAYER 1 — Idempotency key (refresh / retry / double-submit)
         * ============================================================ */
        $idempotencyKey = $request->input('idempotency_key');
    
        if (filled($idempotencyKey)) {
            $existing = DemandeCreationCompte::where('idempotency_key', $idempotencyKey)->first();
    
            if ($existing) {
                return $this->redirectToRdv($request, $existing, 'idempotency_hit');
            }
        }
    
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
    
        $isMineur     = $request->boolean('is_mineur');
        $ocrDocuments = $this->resolveOcrDocuments($request, $isMineur, $validated);
        $ocrFields    = $this->flattenOcrFields($ocrDocuments);
        $mismatches   = $this->hasOcrError($ocrDocuments) ? ['ocr'] : [];
    
        $declaredType = $isMineur
            ? ($validated['piece_identite_representant_type'] ?? null)
            : ($validated['piece_identite_type'] ?? null);
    
        /* ============================================================
         * LAYER 3 — Create + race recovery
         * ============================================================ */
        try {
            $demande = DB::transaction(function () use (
                $request, $validated, $isMineur, $declaredType,
                $ocrFields, $ocrDocuments, $mismatches, $idempotencyKey
            ) {
                $user = $this->createProvisionalUser($validated, $ocrFields);
    
                $firstname = $ocrFields['prenom'] ?? null;
                $lastname  = $ocrFields['nom'] ?? null;
                $name      = trim(implode(' ', array_filter([$firstname, $lastname])));
    
                $payload = collect($validated)
                    ->except([
                        'password',
                        'accept_terms',
                        'piece_identite',
                        'acte_naissance',
                        'piece_identite_representant',
                        'ocr_documents_json',
                        'idempotency_key',
                    ])
                    ->all();
    
                $payload['ocr_fields']    = $ocrFields;
                $payload['ocr_documents'] = $ocrDocuments;
                $payload['username']      = $user->username;
                $payload['name']          = $name !== '' ? $name : $user->name;
    
                $payload['files'] = [
                    'piece_identite'              => $request->file('piece_identite')?->getClientOriginalName(),
                    'acte_naissance'              => $request->file('acte_naissance')?->getClientOriginalName(),
                    'piece_identite_representant' => $request->file('piece_identite_representant')?->getClientOriginalName(),
                ];
    

                $pensionCode = filled($validated['pension_code'] ?? null)
                    ? strtoupper(trim((string) $validated['pension_code']))
                    : null;
    
                $demande = DemandeCreationCompte::create([
                    'code' => CodeGeneratorService::generateUniqueRequestCode(
                        'DEMANDE_CREATION_COMPTE',
                        (new DemandeCreationCompte())->getTable()
                    ),
    
                    'idempotency_key' => $idempotencyKey,
    
                    'user_type' => $validated['user_type'] ?? UserTypeEnum::PENSIONNE->value,
    
                    'email' => filled($validated['email'] ?? null)
                        ? mb_strtolower(trim($validated['email']))
                        : null,
    
                    'username' => $user->username,
                    'name'     => $name !== '' ? $name : $user->name,
    
                    'nif'  => filled($validated['nif'] ?? null)  ? $validated['nif'] : null,
                    'ninu' => filled($validated['ninu'] ?? null) ? trim($validated['ninu']) : null,
    
                    'pension_code' => $pensionCode,
    
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
    
                    'telephone' => $validated['telephone'],
                    'adresse'   => $validated['adresse'],
    
                    'is_mineur' => $isMineur,
    
                    'representant_lien' => $isMineur
                        ? ($validated['representant_lien'] ?? null)
                        : null,
    
                    'piece_identite_representant_type' => $isMineur
                        ? ($validated['piece_identite_representant_type'] ?? null)
                        : null,
    
                    'pieces_identite' => $declaredType ? [$declaredType] : [],
    
                    'ocr_fields'              => $ocrFields,
                    'ocr_documents'           => $ocrDocuments,
                    'verification_mismatches' => $mismatches,
                    'submitted_payload'       => $payload,
    
                    'accepted_terms_at' => now(),
                    'status'            => DemandeCreationCompte::STATUS_EN_ATTENTE,
                    'user_id'           => $user->id,
                ]);
    
                $this->attachIdentityDocuments($request, $demande, $isMineur, $declaredType);
    
                $demande = $demande->fresh();
    
                $demande->recordHistory(
                    DemandeCreationCompteHistory::EVENT_SOUMISE,
                    "Demande de création de compte soumise ({$demande->code}).",
                    $user,
                    DemandeCreationCompte::STATUS_EN_ATTENTE,
                    [
                        'username' => $user->username,
                        'email'    => $user->email,
                    ]
                );
    
                $demande->recordHistory(
                    DemandeCreationCompteHistory::EVENT_COMPTE_CREE,
                    'Compte provisoire créé : accès limité à la prise de rendez-vous.',
                    $user,
                    DemandeCreationCompte::STATUS_EN_ATTENTE,
                    [
                        'user_id'        => $user->id,
                        'account_status' => User::STATUS_EN_ATTENTE_VALIDATION,
                    ]
                );
    
                return $demande;
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
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
        if (
            filled($demande->email) &&
            filter_var($demande->email, FILTER_VALIDATE_EMAIL) &&
            ! str_ends_with($demande->email, '@provisoire.dpc.ht')
        ) {
            $demande->user->notify(new DemandeCreationCompteSoumise($demande));
        }
    
        return $this->redirectToRdv($request, $demande, 'created');
    }

    /**
     * Look for an existing "live" demande matching identifying fields.
     * Strongest identifiers first.
     */
    private function findExistingDemande(array $validated): ?DemandeCreationCompte
    {
        $candidates = array_filter([
            'nif'          => $validated['nif']          ?? null,
            'ninu'         => $validated['ninu']         ?? null,
            'pension_code' => $validated['pension_code'] ?? null,
            'telephone'    => $validated['telephone']    ?? null,
            'email'        => $validated['email']        ?? null,
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
            ->whereNotIn('status', [
                // DemandeCreationCompte::STATUS_REJETEE,
                // DemandeCreationCompte::STATUS_ANNULEE,
            ])
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
        if (! Auth::check() && $demande->user) {
            Auth::login($demande->user);
        }
    
        $url = route('demandes.rencontre.create');
    
        if ($request->expectsJson()) {
            return response()->json([
                'ok'             => true,
                'already_exists' => $reason !== 'created',
                'reason'         => $reason,
                'demande_id'     => $demande->id,
                'redirect_url'   => $url,
            ], 200);
        }
    
        return redirect()->to($url);
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
        $isMineur = $request->boolean('is_mineur');

        $fileRule = 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120';

        $rules = [
            'idempotency_key' => ['nullable', 'string', 'max:64'], 
            'user_type' => 'required|in:pensionne',

            'nif' => [
                'nullable',
                'string',
                new Nif(),
                function (string $attribute, mixed $value, \Closure $fail) {
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
            ],

            'ninu' => [
                'nullable',
                'string',
                new Ninu(),
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

            'pension_code' => [
                'nullable',
                'string',
                new CodePension(),
                new PensionnaireReferenceExists(),
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (
                        User::pensionCodeExists($value) ||
                        DemandeCreationCompte::pendingDigitsMatch(
                            'pension_code',
                            User::normalizeDigits($value)
                        )
                    ) {
                        $fail('Ce code pension est déjà utilisé.');
                    }
                },
            ],

            'telephone' => [
                'required',
                'string',
                new Telephone(),
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
                : 'required|in:permis,passeport,cin',

            'piece_identite' => $isMineur
                ? 'nullable'
                : $fileRule,

            'acte_naissance' => $isMineur
                ? $fileRule
                : 'nullable',

            'representant_lien' => $isMineur
                ? 'required|in:pere,mere,tuteur'
                : 'nullable',

            'piece_identite_representant_type' => $isMineur
                ? 'required|in:permis,passeport,cin'
                : 'nullable',

            'piece_identite_representant' => $isMineur
                ? $fileRule
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
                'nif' => 'NIF',
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
                'password' => 'mot de passe',
                'accept_terms' => 'conditions d’utilisation et politique de confidentialité',
            ]
        );
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

    private function pensionCodeAvailability(string $value): array
    {
        if ($value === '') {
            return [
                'available' => true,
                'empty'     => true,
                'message'   => null,
            ];
        }
    
        // ✅ Normalisation : on travaille sur la version uppercase
        $value = strtoupper(trim($value));
    
        if (! preg_match('/^(7-[A-Z0-9]{5}|8-\d{5})$/', $value)) {
            return [
                'available' => false,
                'format_ok' => false,
                'message'   => 'Le code pension doit être au format 7-XXXXX (ex. 7-JM183) ou 8-XXXXX (ex. 8-34321).',
            ];
        }
    
        $available = ! User::pensionCodeExists($value)
            && ! DemandeCreationCompte::pendingDigitsMatch(
                'pension_code',
                User::normalizeDigits($value)
            );
    
        return [
            'available' => $available,
            'format_ok' => true,
            'message'   => $available
                ? 'Code pension disponible.'
                : 'Ce code pension est déjà utilisé.',
        ];
    }

    private function createProvisionalUser(array $validated, array $ocrFields): User
    {
        $email = filled($validated['email'] ?? null)
            ? mb_strtolower(trim((string) $validated['email']))
            : $this->placeholderEmail($validated);

        $firstname = $ocrFields['prenom'] ?? null;
        $lastname = $ocrFields['nom'] ?? null;

        $name = trim(implode(' ', array_filter([$firstname, $lastname])));

        $usernameSource = $email
            ?: ($validated['pension_code'] ?? null)
            ?: ($validated['telephone'] ?? null)
            ?: 'pensionne';

        $username = User::uniqueUsernameFrom($usernameSource);

        $userType = UserType::firstOrCreate([
            'name' => UserTypeEnum::PENSIONNE->value,
        ]);

        Role::findOrCreate('pensionne', 'web');

        $pensionCode = filled($validated['pension_code'] ?? null)
        ? strtoupper(trim((string) $validated['pension_code']))
        : null;

        $user = User::create([
            'name' => $name !== '' ? $name : $username,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'username' => $username,
            'phone' => $validated['telephone'],
            'password' => $validated['password'],
            'nif' => $validated['nif'] ?? null,
            'pension_code' => $pensionCode,
            'user_type_id' => $userType->id,
            'is_active' => true,
            'account_status' => User::STATUS_EN_ATTENTE_VALIDATION,
        ]);

        $user->assignRole('pensionne');

        return $user;
    }

    private function placeholderEmail(array $validated): string
    {
        $digits = User::normalizeDigits(
            (string) ($validated['pension_code'] ?? $validated['telephone'] ?? 'user')
        );

        $base = 'p' . ($digits !== '' ? $digits : 'user') . '@provisoire.dpc.ht';

        $candidate = $base;
        $suffix = 0;

        while (User::emailExists($candidate)) {
            $suffix++;
            $candidate = 'p' . ($digits !== '' ? $digits : 'user') . $suffix . '@provisoire.dpc.ht';
        }

        return $candidate;
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

        if ($type && isset($collections[$type])) {
            $input = $isMineur ? 'piece_identite_representant' : 'piece_identite';

            if ($request->hasFile($input)) {
                $demande
                    ->addMediaFromRequest($input)
                    ->toMediaCollection($collections[$type], 'public');
            }
        }

        if ($isMineur && $request->hasFile('acte_naissance')) {
            $demande
                ->addMediaFromRequest('acte_naissance')
                ->toMediaCollection('acte_naissance', 'public');
        }
    }
}