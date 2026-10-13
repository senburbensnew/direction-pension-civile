<?php

namespace App\Http\Controllers;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\AgentDelegation;
use App\Models\Demande;
use App\Models\DemandeCreationCompte;
use App\Models\DemandeHistory;
use App\Models\DemandeInteraction;
use App\Models\DemandeMessage;
use App\Models\Service;
use App\Models\TypeDemande;
use App\Models\User;
use App\Models\WorkflowStep;
use App\Notifications\DemandeComplementSoumisNotification;
use App\Services\DemandeWorkflowService;
use App\Services\PersonalDemandeCatalog;
use App\Services\RencontreAvailabilityService;
use App\Services\RencontreMotifService;
use App\Services\RencontreReminderService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;

class PersonalController extends Controller
{
    public function __construct(
        private PersonalDemandeCatalog $demandeCatalog,
    ) {}

    /**
     * Résout l'ID du service de l'utilisateur connecté.
     * Si service_id n'est pas défini, fait un fallback par rôle.
     */
    private function resolveServiceId(): ?int
    {
        $user = auth()->user();

        if ($user->service_id) {
            return $user->service_id;
        }

        foreach (RolesAndPermissionsSeeder::roleToServiceCode() as $role => $code) {
            if ($user->hasRole($role)) {
                return Service::where('code', $code)->value('id');
            }
        }

        return null;
    }

    public function index()
    {
        return view('personal.overview', [
            'stats' => $this->demandeCatalog->statsCardsForUser(),
        ]);
    }

    public function dashboard(Request $request)
    {
        $demandes = Demande::where('created_by', auth()->id())
            ->with('currentStep')
            ->latest()
            ->get();

        // Filtres type / étape — désactivés pour le moment
        // if ($request->filled('step_code')) { ... }
        // if ($request->filled('type')) { ... }
        // $statuses = WorkflowStep::query()...
        // $typesDemandes = TypeDemande::orderBy('label')->get();

        return view('personal.mes-demandes', compact('demandes'));
    }

    public function requestsDashboard(Request $request)
    {
        $requestType = $request->query('request_type');

        if (! is_string($requestType) || $requestType === '') {
            return redirect()->route('personal.dashboard');
        }

        $entry = $this->demandeCatalog->find($requestType);
        if (! $entry) {
            abort(404);
        }

        $baseQuery = Demande::forUser()->ofType($entry['enum']->value);
        $requests = $baseQuery->latest()->paginate(10);
        $stats = $this->demandeQueryStats($baseQuery);
        $type = $entry['label'];

        return view('personal.demandes-by-type', compact('requests', 'stats', 'requestType', 'type'));
    }

    public function showRequestForAuthenticatedUser(Request $request, int $id)
    {
        $demande = Demande::with(['service', 'currentStep.service', 'workflows.toService', 'workflows.fromService', 'user'])
            ->where('created_by', auth()->id())
            ->findOrFail($id);

        $requestHistories = DemandeHistory::where('demande_id', $demande->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $messages = $demande->messages()->with('sender')->get();

        $messages->each(function ($msg) {
            if ($msg->isFromService() && is_null($msg->read_at)) {
                $msg->markAsRead();
            }
        });

        $isClosed = $demande->isRencontre()
            ? $demande->rencontreStatut()->isTerminal()
            : $demande->isClosed();

        return view('personal.request-details', [
            'from' => 'dashboard',
            'request' => $demande,
            'requestHistories' => $requestHistories,
            'activityLogs' => collect(),
            'messages' => $messages,
            'isClosed' => $isClosed,
            'rdvAgentMode' => false,
            'compteProvisional' => null,
        ]);
    }

    /**
     * Réponse de l'usager à une demande de complément.
     */
    public function repondreComplement(Request $request, Demande $demande)
    {
        abort_unless($demande->created_by === auth()->id(), 403);
        abort_unless($demande->needsComplement(), 403, 'Cette action n\'est disponible que pour les demandes en complément requis.');

        $request->validate([
            'message' => 'required|string|max:3000',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $soumiseStepId = WorkflowStep::idForCode('SOUMISE');
            $directionId = Service::where('code', Service::DIRECTION)->value('id');

            DemandeMessage::create([
                'demande_id' => $demande->id,
                'sender_id' => auth()->id(),
                'body' => $request->message,
            ]);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $demande->addMedia($file)
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('complement', 'public');
                }
            }

            $demande->update(array_filter([
                'current_step_id' => $soumiseStepId,
                'current_service_id' => $directionId,
            ]));

            DemandeHistory::create([
                'demande_id' => $demande->id,
                'statut' => 'SOUMISE',
                'commentaire' => 'Réponse de l\'usager — dossier retourné à la Direction : '.$request->message,
                'changed_by' => auth()->id(),
            ]);
        });

        try {
            $demande->loadMissing('currentStep');
            $directionUsers = User::whereHas('service', fn ($q) => $q->where('code', Service::DIRECTION))
                ->orWhereHas('roles', fn ($q) => $q->whereIn('name', User::DIRECTION_ROLES))
                ->get();

            foreach ($directionUsers as $user) {
                $user->notify(new DemandeComplementSoumisNotification($demande));
            }
        } catch (\Throwable $e) {
            Log::error('repondreComplement: direction notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Votre réponse a été envoyée. Le dossier est retourné en traitement.');
    }

    public function corbeille()
    {
        $serviceId = $this->resolveServiceId();

        /*
         * Répertoires visibles dans la corbeille (phase actuelle).
         */
        $folderStats = [
            [
                'key' => 'rencontre',
                'label' => 'Attribution de rendez-vous',
                'icon' => 'fa-calendar-check',
                'color' => 'green',
                'count' => 0,
            ],
            [
                'key' => 'clotures',
                'label' => 'Dossiers clôturés',
                'icon' => 'fa-archive',
                'color' => 'slate',
                'count' => 0,
            ],
        ];

        $currentUser = auth()->user();
        $isAgentRdvOnly = $currentUser?->hasRole('agent_rdv')
            && ! $currentUser->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles());

        $folderStats[0]['count'] = $this->rencontreCorbeilleQuery(
            $serviceId,
            $currentUser,
            $isAgentRdvOnly,
            closedOnly: false
        )->count();

        $folderStats[1]['count'] = $this->rencontreCorbeilleQuery(
            $serviceId,
            $currentUser,
            $isAgentRdvOnly,
            closedOnly: true
        )->count();

        $actingServiceIds = ($serviceId && auth()->user())
            ? AgentDelegation::actingServiceIds(auth()->id(), $serviceId)
            : array_filter([$serviceId]);

        $pendingAffectations = DemandeInteraction::with(['demande.user', 'demande.currentStep'])
            ->where('type', DemandeInteraction::TYPE_AVIS)
            ->whereIn('to_service_id', $actingServiceIds)
            ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
            ->latest()
            ->get();

        $pendingReceptions = DemandeInteraction::with(['demande.currentStep', 'fromService', 'toService'])
            ->where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->whereIn('to_service_id', $actingServiceIds)
            ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
            ->latest()
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Appels de rappel — veille des rencontres
        |--------------------------------------------------------------------------
        |
        | Réservé aux agents RDV : leurs rendez-vous du lendemain à rappeler.
        |
        */

        $appelsVeille = collect();
        $demandesComptePending = collect();
        $user = auth()->user();

        if ($user?->hasRole(User::ROLE_AGENT_RDV)) {
            $reminders = app(RencontreReminderService::class);
            $appelsVeille = $reminders->appointmentsOnForAgent(
                $reminders->reminderDate(),
                (int) $user->id
            );
        }

        if ($user?->hasAnyRole(['admin', User::ROLE_AGENT_FORMALITES])) {
            $demandesComptePending = DemandeCreationCompte::query()
                ->where('status', DemandeCreationCompte::STATUS_EN_ATTENTE)
                ->latest()
                ->limit(15)
                ->get();
        }

        return view('personal.corbeille', compact('folderStats', 'pendingAffectations', 'pendingReceptions', 'appelsVeille', 'demandesComptePending'));
    }

    public function requestsDashboardCorbeille(Request $request)
    {
        $serviceId = $this->resolveServiceId();
        $requestType = $request->input('request_type');

        $map = $this->demandeCatalog->corbeilleTypeMap();

        abort_unless(isset($map[$requestType]), 404);

        $config = $map[$requestType];

        $baseQuery = Demande::ofType($config['enum']->value)
            ->where('current_service_id', $serviceId);

        $requests = $baseQuery->latest()->paginate(10);

        $stats = $this->demandeQueryStats($baseQuery);
        $type = $config['label'];

        return view('personal.dashboard-corbeille', compact(
            'requests',
            'stats',
            'requestType',
            'type'
        ));
    }

    public function showRequest($id)
    {
        $user = auth()->user();

        $requestModel = Demande::with([
            'affectations.toService',
            'currentStep.service',
            'circuitSnapshot',
            'workflows.toService',
            'workflows.fromService',
            'service',
            'user',
        ])->findOrFail($id);

        if ($user && (int) $requestModel->created_by === (int) $user->id) {
            $viewAsAgent = $user->service_id
                && (int) $requestModel->current_service_id === (int) $user->service_id
                && $user->hasAnyRole(RolesAndPermissionsSeeder::internalRoles());

            if (! $viewAsAgent) {
                return $this->showRequestForAuthenticatedUser(request(), (int) $id);
            }
        }

        $workflowService = app(DemandeWorkflowService::class);

        $transferOptions = $workflowService->availableTransferOptions($requestModel, auth()->user());
        $allowedServices = $transferOptions
            ->map(fn ($opt) => (object) ['id' => $opt->service_id, 'nom' => $opt->service_nom])
            ->values();
        $circuitLocked = $workflowService->usesCircuitSnapshot($requestModel);
        $affectations = $requestModel->affectations()->with('toService', 'initiatedBy')->get();

        $actingServiceIds = ($user && $user->service_id)
            ? AgentDelegation::actingServiceIds($user->id, $user->service_id)
            : [];

        $pendingWorkflow = $requestModel->interactions()
            ->with(['fromService', 'toService'])
            ->where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
            ->when(
                count($actingServiceIds) > 0 && ! ($user && $user->hasRole('admin')),
                fn ($q) => $q->whereIn('to_service_id', $actingServiceIds)
            )
            ->latest()
            ->first();

        $pendingAffectation = ($user && $user->service_id)
            ? DemandeInteraction::where('demande_id', $requestModel->id)
                ->where('type', DemandeInteraction::TYPE_AVIS)
                ->where('to_service_id', $user->service_id)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->first()
            : null;

        $isCurrentServiceOwner = $user?->service_id && $requestModel->current_service_id === $user->service_id;
        if (! $isCurrentServiceOwner && ! $user?->hasRole('admin') && ! $user?->isDirection() && $user?->service_id) {
            $submittedAvis = DemandeInteraction::where('demande_id', $requestModel->id)
                ->where('type', DemandeInteraction::TYPE_AVIS)
                ->where('to_service_id', $user->service_id)
                ->whereIn('statut', [DemandeInteraction::STATUT_TERMINE, DemandeInteraction::STATUT_REJETE])
                ->exists();
            abort_if($submittedAvis, 403, 'Votre avis a été soumis. Vous n\'avez plus accès à ce dossier.');
        }

        activity('demande')->performedOn($requestModel)->causedBy(auth()->user())->log('viewed');

        $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $activityLogs = Activity::forSubject($requestModel)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        $messages = $requestModel->messages()->with('sender')->get();

        $messages->each(function ($msg) {
            if (! $msg->isFromService() && is_null($msg->read_at)) {
                $msg->markAsRead();
            }
        });

        // 👇 FIX : distinguer les rencontres (basé sur rdv_statut) du reste (basé sur currentStep)
        $isClosed = $requestModel->isRencontre()
            ? $requestModel->rencontreStatut()->isTerminal()
            : $requestModel->isClosed();

        $availability = app(RencontreAvailabilityService::class);

        $responsableService = $requestModel->currentStep?->service ?? $requestModel->service;

        $canValidateRencontre = method_exists($availability, 'canValidate')
            ? $availability->canValidate($user)
            : (bool) $user?->hasRole(User::ROLE_AGENT_RDV);

        $isAgentRdvOnly = $user?->hasAnyRole([
            User::ROLE_AGENT_RDV,
            User::ROLE_AGENT_FORMALITES,
            'service_accueil_formalites',
        ])
            && ! $user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles());

        $rdvAgentMode = $isAgentRdvOnly && $requestModel->isRencontre();

        $canReassignRencontre = false;
        $canManageRencontreRdv = false;

        if ($requestModel->isRencontre() && $user) {
            $reassignRoles = config(
                'rencontre.reassign_roles',
                array_merge(RolesAndPermissionsSeeder::adminOrDirectionRoles(), ['validateur_rdv'])
            );

            if ($user->hasAnyRole($reassignRoles)) {
                if ($user->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles())) {
                    $canReassignRencontre = true;
                } elseif ($responsableService) {
                    $canReassignRencontre = ! $user->service_id
                        || (int) $user->service_id === (int) $responsableService->id;
                }
            }

            $canManageRencontreRdv = $canReassignRencontre;
        }

        /*
        |--------------------------------------------------------------------------
        | Compte provisoire — analyse post-RDV
        |--------------------------------------------------------------------------
        |
        | Si le RDV est réalisé ET que l'utilisateur est en compte provisoire,
        | on charge la demande de création de compte pour permettre à l'agent
        | d'en faire l'analyse et de la traiter directement depuis ce dossier.
        |
        */
        $compteProvisional = null;
        $rdvScheduler = null;

        if ($requestModel->isRencontre()) {
            $motifKey = (string) ($requestModel->data['motif_key'] ?? '');
            $motifServiceCodes = app(RencontreMotifService::class)->motifServiceCodes();

            $rdvScheduler = [
                'reservedSlotsByService' => $availability->unavailableSlotsByServiceCode(),
                'holidayEvents' => $availability->holidayEvents(
                    now()->startOfDay(),
                    now()->copy()->addMonths(6)->endOfDay()
                ),
                'slotConfig' => array_merge(
                    [
                        'start' => $availability->bookingWindowStart(),
                        'end' => $availability->bookingWindowEnd(),
                        'minutes' => $availability->slotMinutes(),
                    ],
                    $availability->calendarDisplayWindow()
                ),
                'defaultService' => $motifServiceCodes[$motifKey] ?? Service::FORMALITE,
                'allowWeekends' => $availability->allowsWeekends(),
                'bookingDaysLabel' => $availability->bookingDaysLabel(),
                'bookingHoursLabel' => $availability->bookingHoursLabel(),
            ];

            $owner = $requestModel->user ?? User::find($requestModel->created_by);

            if (
                $owner?->isProvisionnel()
                && app(RencontreMotifService::class)->countsTowardAccountDecision($motifKey)
            ) {
                $compteProvisional = DemandeCreationCompte::query()
                    ->where('user_id', $owner->id)
                    ->where('status', DemandeCreationCompte::STATUS_EN_ATTENTE)
                    ->latest()
                    ->first();
            }
        }

        return view('personal.request-details', [
            'from' => 'cart',
            'request' => $requestModel,
            'requestHistories' => $requestHistories,
            'allowedServices' => $allowedServices,
            'transferOptions' => $transferOptions,
            'circuitLocked' => $circuitLocked,
            'affectations' => $affectations,
            'activityLogs' => $activityLogs,
            'messages' => $messages,
            'pendingWorkflow' => $pendingWorkflow,
            'pendingAffectation' => $pendingAffectation,
            'isClosed' => $isClosed,
            'rdvAgents' => $responsableService
                                        ? $availability->agentsAssignableForDemande(
                                            $requestModel,
                                            $responsableService
                                        )
                                        : collect(),
            'canValidateRencontre' => $canValidateRencontre,
            'isAgentRdvOnly' => $isAgentRdvOnly,
            'rdvAgentMode' => $rdvAgentMode,
            'canReassignRencontre' => $canReassignRencontre,
            'canManageRencontreRdv' => $canManageRencontreRdv,
            'rdvTimes' => $requestModel->isRencontre()
                ? $availability->allowedTimes()
                : [],
            'rdvScheduler' => $rdvScheduler,
            'compteProvisional' => $compteProvisional,
        ]);
    }

    public function corbeilleByFolder(Request $request)
    {
        $serviceId = $this->resolveServiceId();
        $folder = $request->input('folder');

        $folders = [
            'rencontre' => 'Attribution de rendez-vous',
            'clotures' => 'Dossiers clôturés',
        ];

        abort_unless(isset($folders[$folder]), 404);

        $currentUser = auth()->user();
        $isAgentRdvOnly = $currentUser?->hasRole('agent_rdv')
            && ! $currentUser->hasAnyRole(RolesAndPermissionsSeeder::adminOrDirectionRoles());

        $closedOnly = $folder === 'clotures';

        $folderScope = function ($q) use ($serviceId, $currentUser, $isAgentRdvOnly, $closedOnly) {
            $this->applyRencontreCorbeilleScope(
                $q,
                $serviceId,
                $currentUser,
                $isAgentRdvOnly,
                $closedOnly
            );
        };

        $requests = Demande::with('currentStep')
            ->where(fn ($q) => $folderScope($q))
            ->latest()
            ->paginate(10);

        $type = $folders[$folder];

        $stats = [];
        foreach (['pending' => 'EN_ATTENTE', 'in_progress' => 'EN_COURS', 'rejected' => 'REJETEE', 'canceled' => 'ANNULEE', 'approved' => 'APPROUVEE', 'completed' => 'FINALISEE'] as $key => $code) {
            $stats[$key] = Demande::where(fn ($q) => $folderScope($q))
                ->whereHas('currentStep', fn ($q) => $q->where('code', $code))
                ->count();
        }

        return view('personal.dashboard-corbeille', compact('requests', 'type', 'folder', 'stats'));
    }

    /**
     * @param  Builder<Demande>  $baseQuery
     * @return array{pending: int, approved: int, in_progress: int, rejected: int, canceled: int, completed: int}
     */
    private function demandeQueryStats($baseQuery): array
    {
        return [
            'pending' => (clone $baseQuery)->pending()->count(),
            'approved' => (clone $baseQuery)->approved()->count(),
            'in_progress' => (clone $baseQuery)->inProgress()->count(),
            'rejected' => (clone $baseQuery)->rejected()->count(),
            'canceled' => (clone $baseQuery)->canceled()->count(),
            'completed' => (clone $baseQuery)->completed()->count(),
        ];
    }

    /**
     * @return Builder<Demande>
     */
    private function rencontreCorbeilleQuery(
        ?int $serviceId,
        ?User $currentUser,
        bool $isAgentRdvOnly,
        bool $closedOnly
    ) {
        $query = Demande::query();

        $this->applyRencontreCorbeilleScope(
            $query,
            $serviceId,
            $currentUser,
            $isAgentRdvOnly,
            $closedOnly
        );

        return $query;
    }

    /**
     * @param  Builder<Demande>  $query
     */
    private function applyRencontreCorbeilleScope(
        $query,
        ?int $serviceId,
        ?User $currentUser,
        bool $isAgentRdvOnly,
        bool $closedOnly
    ): void {
        $query->where('current_service_id', $serviceId)
            ->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value);

        if ($closedOnly) {
            $query->whereIn('data->rdv_statut', RencontreStatutEnum::terminalValues());
        } else {
            $query->where(function ($q) {
                $q->whereNotIn('data->rdv_statut', RencontreStatutEnum::terminalValues())
                    ->orWhereNull('data->rdv_statut');
            });
        }

        if ($isAgentRdvOnly && $currentUser) {
            $query->where('data->agent_id', $currentUser->id);
        }
    }
}
