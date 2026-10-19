<?php

namespace App\Http\Controllers;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Helpers\CodeGeneratorService;
use App\Models\AnneeFiscale;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeHistory;
use App\Models\DirectionDepartementale;
use App\Models\Formalite;
use App\Models\Service;
use App\Models\User;
use App\Rules\Telephone;
use App\Services\DemandeWorkflowService;
use App\Services\RencontreAvailabilityService;
use App\Services\RencontreMotifService;
use App\Services\RencontreReminderService;
use App\Services\RencontreVisioService;
use App\Services\RencontreWorkflowService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DemandeRencontreController extends Controller
{
    public function __construct(
        private RencontreAvailabilityService $availability,
        private RencontreVisioService $visio,
        private RencontreMotifService $motifsService,
        private RencontreWorkflowService $rdvWorkflow,
    ) {}

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401);

        abort_unless(
            $this->motifsService->userCanBookPortalRencontre($user),
            403,
            'Accès réservé aux pensionnés, mandataires et représentants légaux.'
        );

        return view(
            'demandes.rencontre.create',
            $this->bookingViewData($request)
        );
    }

    public function createPhysique(Request $request)
    {
        return redirect()->route(
            'demandes.rencontre.create',
            [
                'modalite' => 'physique',
            ]
        );
    }

    private function bookingViewData(Request $request): array
    {
        $user = $request->user();

        $userMotifs = $this->motifsService->motifsForUser($user);

        $motifServiceCodes = array_intersect_key(
            $this->motifsService->motifServiceCodes(),
            $userMotifs
        );

        return [
            'reservedSlotsByService' => $this->availability->unavailableSlotsByServiceCode(),

            'holidayEvents' => $this->availability->holidayEvents(
                now()->startOfDay(),
                now()->copy()->addMonths(6)->endOfDay()
            ),

            'rdvActif' => $this->activeAppointment($user),

            'mesRendezVous' => Demande::query()
                ->where('created_by', $user->id)
                ->where(
                    'type',
                    TypeDemandeEnum::DEMANDE_RENCONTRE->value
                )
                ->latest()
                ->get(),

            'lieux' => DirectionDepartementale::query()
                ->orderBy('nom')
                ->pluck('nom')
                ->values()
                ->all(),

            'motifs' => $userMotifs,

            'defaultMotif' => $this->defaultMotifForBooking($user),

            'motifServices' => $this->motifsService
                ->motifServiceLabels($user),

            'motifServiceCodes' => $motifServiceCodes,

            'motifDocuments' => collect(
                config('rencontre.motifs', [])
            )->map(fn ($cfg) => $cfg['documents'] ?? [])
                ->all(),

            'allowedTimes' => $this->availability
                ->allowedTimes(),

            'slotConfig' => array_merge(
                [
                    'start' => $this->availability->bookingWindowStart(),
                    'end' => $this->availability->bookingWindowEnd(),
                    'minutes' => $this->availability->slotMinutes(),
                    'daily_capacity' => $this->availability->dailyCapacity(),
                ],
                $this->availability->calendarDisplayWindow()
            ),

            'bookingAgentsReadyByServiceCode' => Service::query()
                ->orderBy('id')
                ->get()
                ->mapWithKeys(fn (Service $service) => [
                    $service->code => $this->availability->hasBookingAgents($service),
                ])
                ->all(),

            'motifPrepareHints' => collect(
                config('rencontre.motifs', [])
            )->mapWithKeys(fn ($cfg, $key) => [
                $key => $cfg['prepare'] ?? [],
            ])->all(),

            'supplementConfig' => config('rencontre.supplement', []),

            'allowWeekends' => $this->availability->allowsWeekends(),

            'bookingDaysLabel' => $this->availability->bookingDaysLabel(),

            'bookingHoursLabel' => $this->availability->bookingHoursLabel(),

            'identite' => $this->identityDefaults($user),
        ];
    }

    private function defaultMotifForBooking(?User $user): string
    {
        $motifs = $this->motifsService->motifsForUser($user);

        if ($this->motifsService->userHasPendingDemandeCreationCompte($user)
            && isset($motifs['demande_formalites_mandats'])) {
            return 'demande_formalites_mandats';
        }

        $requested = (string) request()->query('motif', '');

        if ($requested !== '' && isset($motifs[$requested])) {
            return $requested;
        }

        return array_key_first($motifs) ?? '';
    }

    private function activeAppointment(?User $user): ?Demande
    {
        if (! $user) {
            return null;
        }

        return Demande::query()
            ->where('created_by', $user->id)
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->whereIn(
                'data->rdv_statut',
                [
                    RencontreStatutEnum::DEMANDE->value,
                    RencontreStatutEnum::EN_COURS->value,
                    RencontreStatutEnum::ATTRIBUE->value,
                    RencontreStatutEnum::ACTIF->value,
                    RencontreStatutEnum::REPORTE->value,
                ]
            )
            ->latest()
            ->first();
    }

    private function identityDefaults(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $prenom = $user->formPrenom();
        $nom = $user->formNom();

        if ($prenom === '' && $nom === '') {
            $prenom = (string) (
                $user->firstname
                ?? $user->prenom
                ?? ''
            );
            $nom = (string) (
                $user->lastname
                ?? $user->name
                ?? ''
            );
        }

        return [
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $user->email ?? '',
            'telephone' => $user->telephone
                ?? $user->phone
                ?? '',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CRÉATION
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401);

        abort_unless(
            $this->motifsService->userCanBookPortalRencontre($user),
            403,
            'Accès réservé aux pensionnés, mandataires et représentants légaux.'
        );

        if ($this->activeAppointment($user)) {
            throw ValidationException::withMessages([
                'date_souhaitee' => 'Un rendez-vous est déjà en cours. Annulez-le avant d’en prendre un autre.',
            ]);
        }

        $motifKey = (string) $request->input('motif', '');

        $validated = $request->validate(array_merge([
            'motif' => $this->motifsService->motifValidationRuleForUser($user),

            'modalite' => [
                'required',
                'in:physique,visio',
            ],

            'date_souhaitee' => [
                'required',
                'date',
                'after_or_equal:today',
            ],

            'heure_souhaitee' => [
                'required',
                'date_format:H:i',
            ],

            'lieu_rdv' => [
                'required_if:modalite,physique',
                'nullable',
                'string',
                'max:255',
            ],

            'telephone' => [
                'nullable',
                new Telephone,
            ],

            'message' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'confirmation_lu_accepte' => [
                'required',
                'accepted',
            ],
        ], $this->motifsService->documentValidationRules($motifKey, $user)));

        if (! $this->availability->isBookableDate(
            $validated['date_souhaitee']
        )) {
            throw ValidationException::withMessages([
                'date_souhaitee' => 'Cette date n’est pas ouvrée (week-end ou jour férié en Haïti).',
            ]);
        }

        $service = $this->motifsService->resolve(
            $validated['motif'],
            $user
        );

        if (! $service) {
            throw ValidationException::withMessages([
                'motif' => 'Aucun service responsable n’est configuré pour le motif sélectionné.',
            ]);
        }

        if (! $this->availability->isBookableSlot(
            $validated['date_souhaitee'],
            $validated['heure_souhaitee'],
            $service
        )) {
            throw ValidationException::withMessages([
                'heure_souhaitee' => $this->availability->bookableSlotUserMessage(
                    $validated['date_souhaitee'],
                    $validated['heure_souhaitee'],
                    $service
                ),
            ]);
        }

        $agent = $this->availability->findAvailableAgent(
            $validated['date_souhaitee'],
            $validated['heure_souhaitee'],
            $service
        );

        if (! $agent) {
            throw ValidationException::withMessages([
                'heure_souhaitee' => 'Aucun agent disponible du service « '
                    .$service->nom
                    .' » pour ce créneau.',
            ]);
        }

        $uploadedDocs = [];

        foreach ($request->allFiles() as $key => $file) {
            if (str_starts_with($key, 'doc_')) {
                $uploadedDocs[$key] = $file;
            }
        }

        $demande = DB::transaction(
            function () use (
                $validated,
                $user,
                $service,
                $agent,
                $uploadedDocs
            ) {
                if (
                    ! $this->availability->isBookingAgent(
                        $agent,
                        $service
                    )
                ) {
                    throw ValidationException::withMessages([
                        'heure_souhaitee' => 'L’agent disponible ne correspond plus au service responsable.',
                    ]);
                }

                if (
                    $this->availability->agentHasAppointmentAt(
                        $agent,
                        $validated['date_souhaitee'],
                        $validated['heure_souhaitee']
                    )
                ) {
                    throw ValidationException::withMessages([
                        'heure_souhaitee' => 'Ce créneau vient d’être réservé par un autre rendez-vous.',
                    ]);
                }

                if (
                    $this->availability->dailyAppointmentCount(
                        $agent,
                        $validated['date_souhaitee']
                    ) >= $this->availability->dailyCapacity()
                ) {
                    throw ValidationException::withMessages([
                        'date_souhaitee' => 'La capacité journalière de l’agent est atteinte.',
                    ]);
                }

                $code = CodeGeneratorService::generateUniqueRequestCode(
                    'RENCONTRE',
                    'demandes'
                );

                $motifLabel = $this->motifsService->motifLabel($validated['motif']);

                $data = array_merge($this->identityDefaults($user), [
                    'motif_key' => $validated['motif'],
                    'motif' => $motifLabel,
                    'objet' => $motifLabel,

                    'modalite' => $validated['modalite'],

                    'date_souhaitee' => $validated['date_souhaitee'],

                    'heure_souhaitee' => Demande::normalizeRencontreTime(
                        $validated['heure_souhaitee']
                    ),

                    'telephone' => $validated['telephone']
                        ?? $user->telephone
                        ?? $user->phone
                        ?? null,

                    'message' => $validated['message'] ?? null,

                    'lieu_rdv' => $validated['lieu_rdv'] ?? null,

                    'service_id' => $service->id,

                    'service_code' => $service->code,

                    'service_responsable' => $service->nom,

                    'service_propose' => $motifLabel,

                    'unite' => $service->nom,

                    'agent_id' => $agent->id,

                    'agent_nom' => $agent->displayName(),

                    'documents_a_preparer' => $this->motifsService->prepareHints(
                        $validated['motif'],
                        $validated['modalite']
                    ),
                ]);

                $demande = Demande::create([
                    'code' => $code,

                    'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,

                    'created_by' => $user->id,

                    'current_service_id' => $service->id,

                    'data' => $data,
                ]);

                $demande = $this->rdvWorkflow->enregistrerSoumission(
                    $demande,
                    $user
                );

                $this->motifsService->attachUploadedDocuments(
                    $demande,
                    $validated['motif'],
                    $uploadedDocs
                );

                return $demande;
            }
        );

        $data = $demande->data ?? [];
        $modalite = (string) ($data['modalite'] ?? '');
        $isPhysique = $modalite === 'physique';

        return redirect()
            ->route('demandes.rencontre.create')
            ->with(
                'success',
                'Votre demande de rendez-vous a été enregistrée avec succès.'
            )
            ->with('demande_creee_id', $demande->id)
            ->with('rdv_recap', [
                'code' => $demande->code,
                'type' => $isPhysique ? 'Présentiel' : 'Visioconférence',
                'date' => $data['date_souhaitee'] ?? null,
                'heure' => $data['heure_souhaitee'] ?? null,
                'lieu_ou_lien' => $isPhysique
                    ? ($data['lieu_rdv'] ?? '—')
                    : 'Lien sécurisé — actif 15 minutes avant le rendez-vous',
                'motif' => $data['motif'] ?? $data['objet'] ?? '—',
                'documents' => $data['documents_a_preparer'] ?? [],
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ANNULATION
    |--------------------------------------------------------------------------
    */

    public function annuler(
        Request $request,
        Demande $demande
    ) {
        abort_unless(
            $demande->isRencontre(),
            404
        );

        $user = $request->user();

        abort_unless(
            $this->canCancelDemande($demande, $user),
            403
        );

        $validated = $request->validate([
            'motif' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $this->rdvWorkflow->annuler(
            $demande,
            $user,
            $validated['motif'] ?? null
        );

        return back()->with(
            'success',
            'Le rendez-vous a été annulé.'
        );
    }

    private function canCancelDemande(
        Demande $demande,
        ?User $user
    ): bool {
        if (! $user) {
            return false;
        }

        if ($this->rdvWorkflow->statut($demande)->isTerminal()) {
            return false;
        }

        if ((int) $demande->created_by === (int) $user->id) {
            return true;
        }

        if ($user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles())) {
            return true;
        }

        if ($user->hasRole($this->availability->agentRole())) {
            try {
                $service = $this->responsibleServiceForDemande($demande);

                return $this->availability->isBookingAgent($user, $service);
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /*
    |--------------------------------------------------------------------------
    | VISIO
    |--------------------------------------------------------------------------
    */

    public function visio(
        Request $request,
        string $token
    ) {
        $demande = Demande::query()
            ->with('currentStep')                    // 👈 AJOUT
            ->where('visio_token', $token)
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->firstOrFail();

        $user = $request->user();

        abort_unless(
            $this->visio->canAccess($user, $demande),
            403
        );

        $data = $demande->data ?? [];
        $backUrl = $this->resolveVisioReturnUrl($request, $demande);

        $status = $this->visio->status($demande);

        return view(
            'demandes.rencontre.visio',
            [
                'demande' => $demande,
                'backUrl' => $backUrl,

                'status' => $status,

                'date' => $data['date_souhaitee'] ?? null,

                'heure' => Demande::normalizeRencontreTime(
                    $data['heure_souhaitee'] ?? null
                ),

                'startsAt' => $this->visio->startsAt($demande),

                'opensAt' => $this->visio->opensAt($demande),

                'closesAt' => $this->visio->closesAt($demande),

                'embedUrl' => $status === 'actif'
                    ? $this->visio->embedUrl($demande, $user)
                    : null,
            ]
        );
    }

    public function visioStatus(
        Request $request,
        string $token
    ): JsonResponse {
        $demande = Demande::query()
            ->where('visio_token', $token)
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->firstOrFail();

        abort_unless(
            $this->visio->canAccess($request->user(), $demande),
            403
        );

        return response()->json([
            'status' => $this->visio->status($demande),
            'opens_at' => $this->visio->opensAt($demande)?->toIso8601String(),
            'closes_at' => $this->visio->closesAt($demande)?->toIso8601String(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FORMALITÉ
    |--------------------------------------------------------------------------
    */

    public function formalite(
        Request $request,
        Demande $demande
    ) {
        $this->assertFormaliteAccessible($demande, $request->user());

        $demande->load(['user', 'service']);

        $data = $demande->data ?? [];
        $owner = $demande->user ?? User::find($demande->created_by);

        $formalites = Formalite::query()
            ->where('user_id', $owner?->id)
            ->with(['agent', 'demande', 'anneeFiscale'])
            ->orderByDesc('realisee_at')
            ->get();

        $anneesDispo = AnneeFiscale::ordered();
        $anneeActive = AnneeFiscale::active();

        $formaliteAnneeEnCours = $anneeActive
            ? $formalites->firstWhere('annee_fiscale_id', $anneeActive->id)
            : null;

        return view('demandes.rencontre.formalite', [
            'demande' => $demande,
            'owner' => $owner,
            'rdvStatut' => $this->rdvWorkflow->statut($demande),
            'rdvScheduledAt' => $demande->rencontreScheduledAt(),
            'peutEnregistrer' => $demande->peutEtreTraiteApresRendezVous()
                || ($demande->peutEtreCloture() && ! $demande->formaliteRdvEnregistree()),
            'formalites' => $formalites,
            'anneesDispo' => $anneesDispo,
            'anneeActive' => $anneeActive,
            'formaliteAnneeEnCours' => $formaliteAnneeEnCours,
            'motif' => $data['motif'] ?? '—',
            'cartePension' => $data['carte_pension_path'] ?? null,
        ]);
    }

    public function enregistrerFormalite(
        Request $request,
        Demande $demande
    ) {
        $this->assertFormaliteAccessible($demande, $request->user());

        $user = $request->user();

        if (
            $this->rdvWorkflow->statut($demande) !== RencontreStatutEnum::REALISE
            && $demande->peutEtreCloture()
        ) {
            $this->rdvWorkflow->clore(
                $demande,
                $user,
                RencontreStatutEnum::REALISE,
                'Clôture automatique lors de l’enregistrement de la formalité.'
            );
            $demande = $demande->fresh();
        }

        $this->assertFormaliteEnregistrable($demande);

        $validated = $request->validate([
            'annee_fiscale_id' => [
                'required',
                'integer',
                'exists:annees_fiscales,id',
            ],
            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $owner = $demande->user ?? User::find($demande->created_by);
        $service = $demande->service;

        $anneeFiscale = AnneeFiscale::findOrFail($validated['annee_fiscale_id']);

        $existante = Formalite::forYear($owner->id, $anneeFiscale->id);

        if ($existante) {
            throw ValidationException::withMessages([
                'annee_fiscale_id' => 'Ce pensionné a déjà une formalité enregistrée pour l’exercice '
                    .$anneeFiscale->code.'.',
            ]);
        }

        $formalite = DB::transaction(function () use (
            $demande,
            $owner,
            $service,
            $validated,
            $request,
            $anneeFiscale
        ) {
            $formalite = Formalite::create([
                'user_id' => $owner->id,
                'annee_fiscale_id' => $anneeFiscale->id,
                'realisee_at' => now(),
                'realisee_par' => $request->user()->id,
                'demande_id' => $demande->id,
                'motif_key' => $demande->data['motif_key'] ?? null,
                'service_id' => $service?->id,
                'commentaire' => $validated['commentaire'] ?? null,
            ]);

            DemandeHistory::create([
                'demande_id' => $demande->id,
                'event' => 'FORMALITE_ENREGISTREE',
                'statut' => $this->rdvWorkflow->statut($demande)->value,
                'commentaire' => 'Formalité enregistrée pour l’exercice '
                    .$anneeFiscale->code.'.'
                    .(! empty($validated['commentaire'])
                        ? ' '.$validated['commentaire']
                        : ''),
                'changed_by' => $request->user()->id,
                'champs' => [
                    'action' => 'formalite',
                    'formalite_id' => $formalite->id,
                    'annee_fiscale_id' => $anneeFiscale->id,
                    'annee_fiscale_code' => $anneeFiscale->code,
                ],
            ]);

            return $formalite;
        });

        return redirect()
            ->route('demandes.rencontre.formalite', $demande)
            ->with(
                'success',
                'Formalité enregistrée pour l’exercice '.$anneeFiscale->code.'.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | PILOTAGE
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $this->canAccessPilotage($user),
            403
        );

        $filtre = $request->string('filtre')->toString() ?: 'programmes';

        $query = Demande::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->with([
                'service',
                'currentStep',
                'user',
                'assignments.agent',
            ])
            ->latest();

        if ($request->filled('statut')) {
            $query->whereEffectiveRencontreStatut(
                $request->string('statut')->toString()
            );
        } elseif ($filtre === 'programmes') {
            $query->whereEffectiveRencontreStatutOpen();
        } elseif ($filtre === 'historique') {
            $query->whereEffectiveRencontreStatutClosed();
        }

        if ($request->filled('modalite')) {
            $modalite = $request->string('modalite')->toString();
            if (in_array($modalite, ['physique', 'visio'], true)) {
                $query->where('data->modalite', $modalite);
            }
        }

        if ($request->filled('agent_id')) {
            $query->where('data->agent_id', (int) $request->input('agent_id'));
        }

        if ($request->filled('date_du')) {
            $query->where('data->date_souhaitee', '>=', $request->string('date_du')->toString());
        }

        if ($request->filled('date_au')) {
            $query->where('data->date_souhaitee', '<=', $request->string('date_au')->toString());
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q')->trim()->toString().'%';
            $query->where(function ($q) use ($term) {
                $q->where('code', 'like', $term)
                    ->orWhere('data->prenom', 'like', $term)
                    ->orWhere('data->nom', 'like', $term)
                    ->orWhere('data->telephone', 'like', $term)
                    ->orWhere('data->email', 'like', $term)
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('email', 'like', $term)
                            ->orWhere('firstname', 'like', $term)
                            ->orWhere('lastname', 'like', $term)
                            ->orWhere('name', 'like', $term);
                    });
            });
        }

        $this->scopePilotageListForUser($query, $user);

        $demandes = $query
            ->paginate(20)
            ->withQueryString();

        $align = app(\App\Services\RencontreStatutAlignmentService::class);
        $demandes->setCollection(
            $demandes->getCollection()->map(
                fn (Demande $demande) => $align->alignAfterFormalite($demande, $user)
            )
        );

        $appelsVeille = collect();
        if ($user?->hasRole(User::ROLE_AGENT_RDV)) {
            $reminders = app(RencontreReminderService::class);
            $appelsVeille = $reminders->appointmentsOnForAgent(
                $reminders->reminderDate(),
                (int) $user->id
            );
        }

        return view('demandes.rencontre.pilotage.index', [
            'demandes' => $demandes,
            'filtre' => $filtre,
            'agents' => $this->availability->allBookingAgents(),
            'canValidate' => $this->canManageRencontreWorkflow($user),
            'canReassignInPilotage' => $this->hasPilotageReassignRole($user),
            'canResponsablePilotageActions' => $this->isPilotageReassignOnlyUser($user),
            'reminderData' => $this->reminderData(),
            'statuts' => RencontreStatutEnum::cases(),
            'appelsVeille' => $appelsVeille,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Demande $demande
    ) {
        abort_unless(
            $demande->isRencontre(),
            404
        );

        $user = $request->user();

        abort_unless(
            $this->canAccessDemande(
                $demande,
                $user
            ),
            403
        );

        $demande->load([
            'service',
            'user',
            'currentStep',
            'assignments.agent',
            'histories.changer',
        ]);

        $demande = app(\App\Services\RencontreStatutAlignmentService::class)
            ->alignAfterFormalite($demande, $user);

        $rdvStatut = $this->rdvWorkflow->statut($demande);

        $compteCreation = null;
        $owner = $demande->user ?? User::find($demande->created_by);
        $motifKey = (string) ($demande->data['motif_key'] ?? '');

        if (
            $this->motifsService->countsTowardAccountDecision($motifKey)
            && $owner?->isProvisionnel()
        ) {
            $compteCreation = DemandeCreationCompte::query()
                ->where('user_id', $owner->id)
                ->where('status', DemandeCreationCompte::STATUS_EN_ATTENTE)
                ->latest()
                ->first();
        }

        return view(
            'demandes.rencontre.pilotage.show',
            [
                'demande' => $demande,
                'compteCreation' => $compteCreation,
                'service' => $demande->service,
                'histories' => $demande->histories
                    ->sortByDesc('created_at'),
                'rdvStatus' => $rdvStatut,
                'rdvStatut' => $rdvStatut,
                'statuts' => RencontreStatutEnum::cases(),
                'confirmation' => $this->rdvWorkflow
                    ->confirmation($demande),
                'rdvTimes' => $this->availability->allowedTimes(),
                'allowedTimes' => $this->availability->allowedTimes(),
                'bookingAgents' => $this->availability
                    ->agentsAssignableForDemande(
                        $demande,
                        $this->responsibleServiceForDemande($demande)
                    ),
                'canValidate' => $this->canManageRencontreWorkflow($user),
                'canReassignAgent' => $this->canReassignAgent(
                    $demande,
                    $user
                ),
                'canResponsablePilotageActions' => $this->isPilotageReassignOnlyUser($user)
                    && $this->canReassignAgent($demande, $user),
                'reattribuerRoute' => route(
                    'rencontres.pilotage.reattribuer',
                    $demande
                ),
                'eventLabel' => fn (string $event) => $this->rdvWorkflow->eventLabel($event),
                'dossierShowRoute' => route(
                    'personal.request.show',
                    $demande
                ),
            ]
        );
    }

    /**
     * @param  Builder<Demande>  $query
     */
    private function scopePilotageListForUser($query, User $user): void
    {
        if ($user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles())) {
            return;
        }

        $managerRoles = array_values(array_unique(array_merge(
            [(string) config('rdv.validator_role', 'validateur_rdv')],
            config('rencontre.reassign_roles', [])
        )));

        if ($user->hasAnyRole($managerRoles)) {
            if (! $user->service_id) {
                return;
            }

            $serviceId = (int) $user->service_id;
            $query->where(function ($q) use ($serviceId) {
                $q->where('current_service_id', $serviceId)
                    ->orWhere('data->service_id', $serviceId);
            });

            return;
        }

        $query->where('data->agent_id', $user->id);
    }

    private function canAccessDemande(
        Demande $demande,
        ?User $user
    ): bool {
        if (! $user) {
            return false;
        }

        if (
            (int) $demande->created_by === (int) $user->id
        ) {
            return true;
        }

        if (
            $user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles())
        ) {
            return true;
        }

        $validatorRole = (string) config(
            'rdv.validator_role',
            'validateur_rdv'
        );

        if ($user->hasRole($validatorRole)) {
            try {
                $service = $this->responsibleServiceForDemande(
                    $demande
                );
            } catch (\Throwable) {
                return false;
            }

            if (! $user->service_id) {
                return true;
            }

            return (int) $user->service_id === (int) $service->id;
        }

        if (
            $user->hasRole(
                $this->availability->agentRole()
            )
        ) {
            if (
                (int) ($demande->data['agent_id'] ?? 0)
                === (int) $user->id
            ) {
                return true;
            }

            try {
                $service = $this->responsibleServiceForDemande(
                    $demande
                );

                return $this->availability->isBookingAgent(
                    $user,
                    $service
                );
            } catch (\Exception $e) {
                return false;
            }
        }

        if ($this->canReassignAgent($demande, $user)) {
            return true;
        }

        return false;
    }

    private function pilotageReassignOnlyRoles(): array
    {
        return config(
            'rencontre.pilotage_reassign_only_roles',
            ['responsable_service_accueil_formalites']
        );
    }

    private function hasPilotageReassignRole(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(
            config(
                'rencontre.reassign_roles',
                array_merge(RolesAndPermissionsSeeder::adminOrDirectionRoles(), ['validateur_rdv'])
            )
        );
    }

    private function isPilotageReassignOnlyUser(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        $onlyRoles = $this->pilotageReassignOnlyRoles();

        if ($onlyRoles === []) {
            return false;
        }

        return $user->hasAnyRole($onlyRoles)
            && ! $this->canManageRencontreWorkflow($user);
    }

    private function canAccessPilotage(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles())) {
            return true;
        }

        if ($this->canManageRencontreWorkflow($user)) {
            return true;
        }

        return $user->hasAnyRole($this->pilotageReassignOnlyRoles());
    }

    private function canReassignAgent(
        Demande $demande,
        ?User $user
    ): bool {
        if (! $user) {
            return false;
        }

        $roles = config(
            'rencontre.reassign_roles',
            array_merge(RolesAndPermissionsSeeder::adminOrDirectionRoles(), ['validateur_rdv'])
        );

        if (! $user->hasAnyRole($roles)) {
            return false;
        }

        if ($user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles())) {
            return true;
        }

        try {
            $service = $this->responsibleServiceForDemande($demande);
        } catch (\Throwable) {
            return false;
        }

        if (! $user->service_id) {
            return true;
        }

        return (int) $user->service_id === (int) $service->id;
    }

    private function canValidate(?User $user): bool
    {
        return $this->canManageRencontreWorkflow($user);
    }

    private function canManageRencontreWorkflow(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->hasAnyRole(array_merge(
            RolesAndPermissionsSeeder::adminOrDirectionRoles(),
            [
                $this->availability->agentRole(),
                (string) config('rdv.validator_role', 'validateur_rdv'),
            ]
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | EXAMEN
    |--------------------------------------------------------------------------
    */

    public function examiner(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $this->assertNotTerminal($demande);

        $this->rdvWorkflow->examiner(
            $demande,
            $request->user()
        );

        return back()->with(
            'success',
            'La demande a été prise en charge pour examen.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | ATTRIBUTION
    |--------------------------------------------------------------------------
    */

    public function attribuer(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $this->assertNotTerminal($demande);

        $validated = $request->validate([
            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $this->rdvWorkflow->attribuer(
            $demande,
            $request->user(),
            $validated['commentaire'] ?? null
        );

        // 👇 AJOUT : générer le token visio à l'attribution
        $demande->refresh();
        if (($demande->data['modalite'] ?? null) === 'visio') {
            $this->visio->attachTo($demande);
        }

        return back()->with(
            'success',
            'Le créneau a été attribué.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | PROPOSITION DE CRÉNEAU
    |--------------------------------------------------------------------------
    */

    public function proposerCreneau(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $this->assertNotTerminal($demande);

        $validated = $request->validate([
            'date_souhaitee' => [
                'required',
                'date',
            ],

            'heure_souhaitee' => [
                'required',
                'date_format:H:i',
            ],

            'agent_id' => [
                'nullable',
                'integer',
            ],

            'lieu_rdv' => [
                'nullable',
                'string',
                'max:255',
            ],

            'report' => [
                'nullable',
                'boolean',
            ],
        ]);

        $service = $this->responsibleServiceForDemande(
            $demande
        );

        if (! empty($validated['agent_id'])) {
            $agent = User::find(
                $validated['agent_id']
            );

            if (
                ! $agent
                || ! $this->availability->isBookingAgent(
                    $agent,
                    $service
                )
            ) {
                throw ValidationException::withMessages([
                    'agent_id' => 'L’agent sélectionné n’appartient pas au service responsable.',
                ]);
            }
        }

        if (! $this->availability->isBookableDate(
            $validated['date_souhaitee']
        )) {
            throw ValidationException::withMessages([
                'date_souhaitee' => 'Cette date n’est pas ouvrée (week-end ou jour férié en Haïti).',
            ]);
        }

        if (! empty($validated['agent_id'])) {
            $targetAgent = User::find($validated['agent_id']);

            if (! $targetAgent) {
                throw ValidationException::withMessages([
                    'agent_id' => 'L’agent sélectionné est introuvable.',
                ]);
            }

            $blockReason = $this->availability->agentAssignmentBlockReason(
                $targetAgent,
                $service,
                $validated['date_souhaitee'],
                $validated['heure_souhaitee'],
                $demande
            );

            if ($blockReason !== null) {
                throw ValidationException::withMessages([
                    'agent_id' => $blockReason,
                ]);
            }
        } elseif (! $this->availability->isBookableSlot(
            $validated['date_souhaitee'],
            $validated['heure_souhaitee'],
            $service
        )) {
            throw ValidationException::withMessages([
                'heure_souhaitee' => 'Aucun agent du service « '
                    .$service->nom
                    .' » n’est disponible pour ce créneau.',
            ]);
        }

        $this->rdvWorkflow->proposerCreneau(
            $demande,
            $request->user(),
            [
                'date_souhaitee' => $validated['date_souhaitee'],
                'heure_souhaitee' => $validated['heure_souhaitee'],
                'agent_id' => $validated['agent_id'] ?? null,
                'lieu_rdv' => $validated['lieu_rdv'] ?? null,
            ],
            (bool) ($validated['report'] ?? false)
        );

        return back()->with(
            'success',
            'Le nouveau créneau a été enregistré.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    public function accepter(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $this->assertNotTerminal($demande);

        $this->rdvWorkflow->valider(
            $demande,
            $request->user()
        );

        // 👇 AJOUT : générer le token visio à la validation
        $demande->refresh();
        if (($demande->data['modalite'] ?? null) === 'visio') {
            $this->visio->attachTo($demande);
        }

        return back()->with(
            'success',
            'Le rendez-vous a été validé définitivement.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | REFUS
    |--------------------------------------------------------------------------
    */

    public function refuser(
        Request $request,
        Demande $demande
    ) {
        $user = $request->user();

        abort_unless(
            $demande->isRencontre(),
            404
        );

        if ($this->canManageRencontreWorkflow($user)) {
            $this->authorizeRdvAgent($demande, $user);
        } else {
            abort_unless(
                $this->canReassignAgent($demande, $user),
                403
            );
        }

        $this->assertNotTerminal($demande);

        $request->merge([
            'commentaire' => $request->input('commentaire')
                ?: $request->input('motif'),
        ]);

        $validated = $request->validate([
            'commentaire' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $this->rdvWorkflow->apply(
            $demande,
            RencontreStatutEnum::REFUSE,
            $request->user(),
            'REJECTED',
            $validated['commentaire'],
            [
                'refuse_par' => $request->user()->id,

                'refuse_at' => now()->toIso8601String(),
            ],
            [
                'action' => 'refus',
            ]
        );

        $demande->refresh();

        if ($this->rdvWorkflow->statut($demande) !== RencontreStatutEnum::REFUSE) {
            return back()->with(
                'error',
                'Le refus n’a pas pu être appliqué (statut actuel : '
                .$this->rdvWorkflow->statut($demande)->value.').'
            );
        }

        return back()->with(
            'success',
            'La demande a été refusée.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLOTURE
    |--------------------------------------------------------------------------
    */

    public function clore(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $this->assertNotTerminal($demande);

        $validated = $request->validate([
            'statut' => [
                'required',
                'in:realise,non_honore',
            ],

            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $statut = RencontreStatutEnum::from(
            $validated['statut']
        );

        abort_unless(
            $demande->peutEtreCloture(),
            422,
            $demande->rencontreScheduledAt()
                ? 'La clôture n’est possible qu’après l’heure prévue du rendez-vous ('
                .$demande->rencontreScheduledAt()->format('d/m/Y à H:i')
                .').'
                : 'La clôture n’est pas possible pour ce rendez-vous.'
        );

        $this->rdvWorkflow->clore(
            $demande,
            $request->user(),
            $statut,
            $validated['commentaire'] ?? null
        );

        return back()->with(
            'success',
            'Le rendez-vous a été clôturé.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RÉATTRIBUTION D’AGENT (chef de service / validateur)
    |--------------------------------------------------------------------------
    */

    public function reattribuerAgent(
        Request $request,
        Demande $demande
    ) {
        abort_unless(
            $demande->isRencontre(),
            404
        );

        $user = $request->user();

        abort_unless(
            $this->canReassignAgent($demande, $user),
            403
        );

        $this->assertNotTerminal($demande);

        $service = $this->responsibleServiceForDemande($demande);

        $validated = $request->validate([
            'agent_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'commentaire' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $agent = User::findOrFail($validated['agent_id']);

        if (! $this->availability->isBookingAgent($agent, $service)) {
            throw ValidationException::withMessages([
                'agent_id' => 'L’agent sélectionné n’appartient pas au service responsable.',
            ]);
        }

        $date = $demande->data['date_souhaitee'] ?? null;
        $heure = $demande->data['heure_souhaitee'] ?? null;

        if (! $date || ! $heure) {
            throw ValidationException::withMessages([
                'agent_id' => 'Impossible de réattribuer : date ou heure du rendez-vous manquante.',
            ]);
        }

        $blockReason = $this->availability->agentAssignmentBlockReason(
            $agent,
            $service,
            $date,
            $heure,
            $demande
        );

        if ($blockReason !== null) {
            throw ValidationException::withMessages([
                'agent_id' => $blockReason,
            ]);
        }

        $this->availability->routeToResponsibleService(
            $demande,
            $service,
            $agent,
            $user,
            app(DemandeWorkflowService::class)
        );

        DemandeHistory::create([
            'demande_id' => $demande->id,
            'event' => 'RDV_REASSIGNED',
            'statut' => $this->rdvWorkflow->statut($demande)->value,
            'commentaire' => $validated['commentaire']
                ?? 'Réattribution à un autre agent.',
            'changed_by' => $user->id,
            'champs' => [
                'action' => 'reattribution_agent',
                'agent_id' => $agent->id,
                'agent_nom' => $agent->displayName(),
            ],
        ]);

        return back()->with(
            'success',
            'Le rendez-vous a été réattribué à '
            .$agent->displayName()
            .'.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SUITE
    |--------------------------------------------------------------------------
    */

    public function enregistrerSuite(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $validated = $request->validate([
            'suite' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $this->rdvWorkflow->apply(
            $demande,
            $this->rdvWorkflow->statut($demande),
            $request->user(),
            'SUITE',
            $validated['suite'],
            [
                'suite' => $validated['suite'],

                'suite_par' => $request->user()->id,

                'suite_at' => now()->toIso8601String(),
            ],
            [
                'action' => 'suite',
            ]
        );

        return back()->with(
            'success',
            'La suite donnée a été enregistrée.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | IDENTITÉ
    |--------------------------------------------------------------------------
    */

    public function confirmerIdentite(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $this->assertNotTerminal($demande);

        $this->rdvWorkflow->apply(
            $demande,
            $this->rdvWorkflow->statut($demande),
            $request->user(),
            'IDENTITY_CONFIRMED',
            'Identité du demandeur confirmée.',
            [
                'identite_confirmee' => true,

                'identite_confirmee_par' => $request->user()->id,

                'identite_confirmee_at' => now()->toIso8601String(),
            ],
            [
                'action' => 'confirmation_identite',
            ]
        );

        return back()->with(
            'success',
            'Identité confirmée.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RECAP
    |--------------------------------------------------------------------------
    */

    public function recapPayload(
        Demande $demande
    ): array {
        abort_unless(
            $demande->isRencontre(),
            404
        );

        return $this->rdvWorkflow
            ->confirmation($demande);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function responsibleServiceForDemande(
        Demande $demande
    ): Service {
        $demande->loadMissing([
            'service',
            'user',
        ]);

        if ($demande->service) {
            return $demande->service;
        }

        $motif =
            $demande->data['motif_key']
            ?? $demande->data['motif']
            ?? null;

        if (! $motif) {
            throw ValidationException::withMessages([
                'motif' => 'Impossible de déterminer le service responsable : le motif est absent.',
            ]);
        }

        $service = $this->motifsService->resolve(
            $motif,
            $demande->user
        );

        if (! $service) {
            throw ValidationException::withMessages([
                'motif' => 'Aucun service responsable n’est configuré pour ce motif.',
            ]);
        }

        return $service;
    }

    private function authorizeRdvAgent(
        Demande $demande,
        ?User $user
    ): void {
        abort_unless(
            $demande->isRencontre(),
            404
        );

        abort_unless(
            $user && $this->canManageRencontreWorkflow($user),
            403
        );

        if ($user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles())) {
            return;
        }

        $validatorRole = (string) config(
            'rdv.validator_role',
            'validateur_rdv'
        );

        if ($user->hasRole($validatorRole)) {
            $service = $this->responsibleServiceForDemande(
                $demande
            );

            if ($user->service_id) {
                abort_unless(
                    (int) $user->service_id === (int) $service->id,
                    403
                );
            }

            return;
        }

        if (
            $user->hasRole(
                $this->availability->agentRole()
            )
        ) {
            $service =
                $this->responsibleServiceForDemande(
                    $demande
                );

            abort_unless(
                $this->availability->isBookingAgent(
                    $user,
                    $service
                ),
                403
            );
        }
    }

    private function assertNotTerminal(Demande $demande): void
    {
        abort_if(
            $this->rdvWorkflow->statut($demande)->isTerminal(),
            422,
            'Ce rendez-vous est déjà clos.'
        );
    }

    private function assertFormaliteAccessible(
        Demande $demande,
        ?User $user
    ): void {
        abort_unless($demande->isRencontre(), 404);

        $this->authorizeRdvAgent($demande, $user);

        $statut = $this->rdvWorkflow->statut($demande);

        abort_unless(
            $statut === RencontreStatutEnum::REALISE
            || $demande->peutEtreCloture(),
            422,
            'Le rendez-vous doit d’abord être marqué comme réalisé, ou le créneau doit être passé.'
        );

        $motifKey = (string) ($demande->data['motif_key'] ?? '');
        $isFormalite = str_starts_with($motifKey, 'demande_formalites')
            || str_starts_with($motifKey, 'formalites');

        abort_unless(
            $isFormalite,
            422,
            'Ce rendez-vous n’est pas lié à une démarche de formalité.'
        );

        $owner = $demande->user ?? User::find($demande->created_by);

        abort_if(
            $owner?->isProvisionnel(),
            403,
            'Cette démarche est indisponible pour un compte provisoire.'
        );
    }

    private function assertFormaliteEnregistrable(Demande $demande): void
    {
        abort_unless(
            $demande->peutEtreTraiteApresRendezVous()
            || $demande->peutEtreCloture(),
            422,
            $demande->rencontreScheduledAt()
                ? 'L’enregistrement n’est possible qu’après l’heure prévue du rendez-vous ('
                .$demande->rencontreScheduledAt()->format('d/m/Y à H:i')
                .').'
                : 'L’enregistrement n’est pas possible pour ce rendez-vous.'
        );
    }

    private function reminderData(): array
    {
        return [
            'enabled' => true,
        ];
    }

    /**
     * Page de retour depuis la visio (?back= create|pilotage|dossier, mémorisé en session).
     */
    private function resolveVisioReturnUrl(
        Request $request,
        Demande $demande
    ): string {
        $sessionKey = 'visio_back.'.$demande->visio_token;
        $user = $request->user();
        $back = $request->query('back');

        if (is_string($back) && in_array($back, ['create', 'pilotage', 'dossier', 'corbeille'], true)) {
            session([$sessionKey => $back]);
        } elseif (
            $user
            && (int) $demande->created_by === (int) $user->id
            && $back !== 'pilotage'
        ) {
            session([$sessionKey => 'create']);
            $back = 'create';
        } else {
            $back = session($sessionKey);
        }

        $url = match ($back) {
            'create' => route('demandes.rencontre.create'),
            'pilotage' => route('rencontres.pilotage.show', $demande),
            'dossier' => route('personal.request.show', $demande->id),
            'corbeille' => $this->visioCorbeilleRencontreUrl(),
            default => $this->defaultVisioReturnUrl($request, $demande),
        };

        return $this->finalizeVisioBackUrl($user, $url);
    }

    private function visioCorbeilleRencontreUrl(): string
    {
        return route('personal.cart.folder', ['folder' => 'rencontre']);
    }

    /**
     * Les agents RDV retournent toujours vers le dossier « rencontre » de la corbeille.
     */
    private function finalizeVisioBackUrl(?User $user, string $url): string
    {
        if (
            $user?->hasRole(User::ROLE_AGENT_RDV)
            && ! $user->hasAnyRole(array_merge(
                RolesAndPermissionsSeeder::adminOrDirectionRoles(),
                [User::ROLE_VALIDATEUR_RDV]
            ))
        ) {
            return $this->visioCorbeilleRencontreUrl();
        }

        return $url;
    }

    private function defaultVisioReturnUrl(
        Request $request,
        Demande $demande
    ): string {
        $user = $request->user();

        if ($user && (int) $demande->created_by === (int) $user->id) {
            return route('demandes.rencontre.create');
        }

        if ($user?->hasRole('pensionne')) {
            return route('demandes.rencontre.create');
        }

        if ($user?->hasAnyRole([
            User::ROLE_AGENT_RDV,
            User::ROLE_AGENT_FORMALITES,
        ])) {
            return $this->visioCorbeilleRencontreUrl();
        }

        if ($user?->hasAnyRole(array_merge(
            RolesAndPermissionsSeeder::adminOrDirectionRoles(),
            [User::ROLE_VALIDATEUR_RDV]
        ))) {
            return route('rencontres.pilotage.show', $demande);
        }

        return route('personal.request.show', $demande->id);
    }
}
