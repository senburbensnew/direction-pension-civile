<?php

namespace App\Http\Controllers;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonalController extends Controller
{
    public function __construct(
        private PersonalDemandeCatalog $demandeCatalog,
    ) {
    }

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

        $roleToService = [
            'direction'                  => Service::DIRECTION,
            'secretariat'                => Service::SECRETARIAT,
            'service_liquidation'        => Service::LIQUIDATION,
            'service_controle_placement' => Service::CONTROLE_PLACEMENT,
            'service_comptabilite'       => Service::COMPTABILITE,
            'service_accueil_formalites' => Service::FORMALITE,
            'service_formalite'          => Service::FORMALITE,
            'service_assurance'          => Service::ASSURANCE,
        ];

        foreach ($roleToService as $role => $code) {
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
        $demande = Demande::with(['service', 'currentStep.service', 'workflows.toService', 'workflows.fromService'])
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
            'from'             => 'dashboard',
            'request'          => $demande,
            'requestHistories' => $requestHistories,
            'activityLogs'     => collect(),
            'messages'         => $messages,
            'isClosed'         => $isClosed,
            'rdvAgentMode'     => false,
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
            'message'      => 'required|string|max:3000',
            'documents'    => 'nullable|array|max:10',
            'documents.*'  => 'file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $soumiseStepId = WorkflowStep::idForCode('SOUMISE');
            $directionId   = Service::where('code', Service::DIRECTION)->value('id');

            DemandeMessage::create([
                'demande_id' => $demande->id,
                'sender_id'  => auth()->id(),
                'body'       => $request->message,
            ]);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $demande->addMedia($file)
                        ->usingFileName($file->getClientOriginalName())
                        ->toMediaCollection('complement', 'public');
                }
            }

            $demande->update(array_filter([
                'current_step_id'    => $soumiseStepId,
                'current_service_id' => $directionId,
            ]));

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => 'SOUMISE',
                'commentaire' => 'Réponse de l\'usager — dossier retourné à la Direction : ' . $request->message,
                'changed_by'  => auth()->id(),
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

        $folderStats = [
            ['key' => 'urgent',          'label' => 'Dossiers urgents',           'icon' => 'fa-exclamation-triangle', 'color' => 'red'],
            ['key' => 'pension',         'label' => 'Demandes de pension',        'icon' => 'fa-file-alt',             'color' => 'blue'],
            ['key' => 'prestations',     'label' => 'Demandes de prestations',    'icon' => 'fa-money-bill-wave',      'color' => 'yellow'],
            ['key' => 'administratif',   'label' => 'Dossiers administratifs',    'icon' => 'fa-folder',               'color' => 'indigo'],
            ['key' => 'correspondances', 'label' => 'Correspondances',            'icon' => 'fa-envelope',             'color' => 'purple'],
            ['key' => 'rencontre',       'label' => 'Attribution de rendez-vous', 'icon' => 'fa-calendar-check',       'color' => 'green'],
            ['key' => 'autres',          'label' => 'Autres',                     'icon' => 'fa-ellipsis-h',           'color' => 'gray'],
            ['key' => 'clotures',        'label' => 'Dossiers clôturés',          'icon' => 'fa-archive',              'color' => 'teal'],
        ];

        $currentUser = auth()->user();
        $isAgentRdvOnly = $currentUser?->hasRole('agent_rdv')
            && ! $currentUser->hasAnyRole(['admin', 'direction']);

            foreach ($folderStats as &$folder) {
                $query = Demande::where('current_service_id', $serviceId);
            
                /*
                |--------------------------------------------------------------------------
                | Filtre global agent RDV
                |--------------------------------------------------------------------------
                |
                | Un agent RDV ne voit QUE les dossiers qui lui sont attribués,
                | quel que soit le type (rencontre, pension, prestation…).
                |
                */
                if ($isAgentRdvOnly) {
                    $query->where('data->agent_id', $currentUser->id);
                }
            
                if ($folder['key'] === 'clotures') {
                    $query->where(function ($q) {
                        $q->where(function ($q2) {
                            $q2->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                               ->whereIn('data->rdv_statut', RencontreStatutEnum::terminalValues());
                        })
                        ->orWhere(function ($q2) {
                            $q2->where('type', '!=', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                               ->whereHas('currentStep', fn ($s) => $s->whereIn('code', [
                                   'FINALISEE', 'REJETEE', 'ANNULEE',
                               ]));
                        });
                    });
            
                } elseif ($folder['key'] === 'urgent') {
                    $query->where(function ($q) {
                        $q->where(function ($q2) {
                            $q2->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                               ->where(function ($q3) {
                                   $q3->whereNotIn('data->rdv_statut', RencontreStatutEnum::terminalValues())
                                      ->orWhereNull('data->rdv_statut');
                               });
                        })->orWhere(function ($q2) {
                            $q2->where('type', '!=', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                               ->whereHas('currentStep', fn ($s) => $s->whereNotIn('code', [
                                   'FINALISEE', 'REJETEE', 'ANNULEE',
                               ]));
                        });
                    })->where(function ($q) {
                        $q->where('is_urgent', true)
                          ->orWhere('submitted_at', '<=', now()->subDays(30));
                    });
            
                } elseif ($folder['key'] === 'rencontre') {
                    $query->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                          ->where(function ($q) {
                              $q->whereNotIn('data->rdv_statut', RencontreStatutEnum::terminalValues())
                                ->orWhereNull('data->rdv_statut');
                          });
            
                } else {
                    $query->where('type', '!=', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                          ->whereHas('currentStep', fn ($s) => $s->whereNotIn('code', [
                              'FINALISEE', 'REJETEE', 'ANNULEE',
                          ]))
                          ->where('categorie', $folder['key']);
                }
            
                $folder['count'] = $query->count();
            }
        unset($folder);

        $actingServiceIds = ($serviceId && auth()->user())
            ? \App\Models\AgentDelegation::actingServiceIds(auth()->id(), $serviceId)
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
        | Un agent RDV ne voit que ses propres rendez-vous.
        | Direction / admin voient l'ensemble.
        |
        */

        $appelsVeille = collect();
        $demandesComptePending = collect();
        $user = auth()->user();

        if ($user?->hasAnyRole([User::ROLE_AGENT_RDV, 'service_accueil_formalites', 'direction', 'admin'])) {
            $reminders = app(\App\Services\RencontreReminderService::class);
            $date      = $reminders->reminderDate();

            $isAgentRdvOnly = $user->hasRole(User::ROLE_AGENT_RDV)
                && ! $user->hasAnyRole(['admin', 'direction', 'directeur', 'assistant_directeur']);

            $appelsVeille = $isAgentRdvOnly
                ? $reminders->appointmentsOnForAgent($date, $user->id)
                : $reminders->appointmentsOn($date);
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
        $serviceId   = $this->resolveServiceId();
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
        ])->findOrFail($id);

        if ($user && (int) $requestModel->created_by === (int) $user->id) {
            $viewAsAgent = $user->service_id
                && (int) $requestModel->current_service_id === (int) $user->service_id
                && $user->hasAnyRole([
                    'secretariat',
                    'direction',
                    'service_liquidation',
                    'service_accueil_formalites',
                    'service_formalite',
                    'service_controle_placement',
                    'service_comptabilite',
                    'service_assurance',
                    'admin',
                    User::ROLE_AGENT_RDV,
                    User::ROLE_AGENT_FORMALITES,
                    User::ROLE_VALIDATEUR_RDV,
                ]);

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
            ? \App\Models\AgentDelegation::actingServiceIds($user->id, $user->service_id)
            : [];

        $pendingWorkflow = $requestModel->interactions()
            ->with(['fromService', 'toService'])
            ->where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
            ->when(
                count($actingServiceIds) > 0 && !($user && $user->hasRole('admin')),
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
        if (!$isCurrentServiceOwner && !$user?->hasRole('admin') && !$user?->isDirection() && $user?->service_id) {
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

        $activityLogs = \Spatie\Activitylog\Models\Activity::forSubject($requestModel)
            ->with('causer')
            ->latest()
            ->take(20)
            ->get();

        $messages = $requestModel->messages()->with('sender')->get();

        $messages->each(function ($msg) {
            if (!$msg->isFromService() && is_null($msg->read_at)) {
                $msg->markAsRead();
            }
        });

        // 👇 FIX : distinguer les rencontres (basé sur rdv_statut) du reste (basé sur currentStep)
        $isClosed = $requestModel->isRencontre()
            ? $requestModel->rencontreStatut()->isTerminal()
            : $requestModel->isClosed();

        $availability = app(\App\Services\RencontreAvailabilityService::class);

        $responsableService = $requestModel->currentStep?->service ?? $requestModel->service;

        $canValidateRencontre = method_exists($availability, 'canValidate')
            ? $availability->canValidate($user)
            : (bool) $user?->hasRole(User::ROLE_AGENT_RDV);

        $isAgentRdvOnly = $user?->hasAnyRole([
                User::ROLE_AGENT_RDV,
                User::ROLE_AGENT_FORMALITES,
                'service_accueil_formalites',
            ])
            && ! $user->hasAnyRole(['admin', 'direction']);

        $rdvAgentMode = $isAgentRdvOnly && $requestModel->isRencontre();

        $canReassignRencontre = false;
        $canManageRencontreRdv = false;

        if ($requestModel->isRencontre() && $user) {
            $reassignRoles = config(
                'rencontre.reassign_roles',
                ['admin', 'direction', 'validateur_rdv']
            );

            if ($user->hasAnyRole($reassignRoles)) {
                if ($user->hasAnyRole(['admin', 'direction'])) {
                    $canReassignRencontre = true;
                } elseif ($responsableService) {
                    $canReassignRencontre = !$user->service_id
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

        if ($requestModel->isRencontre()) {
            $owner = $requestModel->user ?? User::find($requestModel->created_by);
            $motifKey = (string) ($requestModel->data['motif_key'] ?? '');

            if (
                $owner?->isProvisionnel()
                && app(\App\Services\RencontreMotifService::class)->countsTowardAccountDecision($motifKey)
            ) {
                $compteProvisional = \App\Models\DemandeCreationCompte::query()
                    ->where('user_id', $owner->id)
                    ->where('status', \App\Models\DemandeCreationCompte::STATUS_EN_ATTENTE)
                    ->latest()
                    ->first();
            }
        }

        return view('personal.request-details', [
            'from'                  => 'cart',
            'request'               => $requestModel,
            'requestHistories'      => $requestHistories,
            'allowedServices'       => $allowedServices,
            'transferOptions'       => $transferOptions,
            'circuitLocked'         => $circuitLocked,
            'affectations'          => $affectations,
            'activityLogs'          => $activityLogs,
            'messages'              => $messages,
            'pendingWorkflow'       => $pendingWorkflow,
            'pendingAffectation'    => $pendingAffectation,
            'isClosed'              => $isClosed,
            'rdvAgents'             => $responsableService
                                        ? $availability->agentsAssignableForDemande(
                                            $requestModel,
                                            $responsableService
                                        )
                                        : collect(),
            'canValidateRencontre'  => $canValidateRencontre,
            'isAgentRdvOnly'        => $isAgentRdvOnly,
            'rdvAgentMode'          => $rdvAgentMode,
            'canReassignRencontre'  => $canReassignRencontre,
            'canManageRencontreRdv' => $canManageRencontreRdv,
            'rdvTimes'              => $requestModel->isRencontre()
                ? $availability->allowedTimes()
                : [],
            'compteProvisional' => $compteProvisional,
        ]);
    }

    public function corbeilleByFolder(Request $request)
    {
        $serviceId = $this->resolveServiceId();
        $folder    = $request->input('folder');

        $folders = [
            'urgent'          => 'Dossiers urgents',
            'pension'         => 'Demandes de pension',
            'prestations'     => 'Demandes de prestations',
            'administratif'   => 'Dossiers administratifs',
            'correspondances' => 'Correspondances',
            'rencontre'       => 'Attribution de rendez-vous',
            'autres'          => 'Autres',
            'clotures'        => 'Dossiers clôturés',
        ];

        abort_unless(isset($folders[$folder]), 404);

        $userId = auth()->id();

        $folderScope = function ($q) use ($folder, $serviceId, $userId) {
            $q->where('current_service_id', $serviceId);

            if ($folder === 'clotures') {
                $q->where(function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                           ->whereIn('data->rdv_statut', RencontreStatutEnum::terminalValues());
                    })->orWhere(function ($q3) {
                        $q3->where('type', '!=', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                           ->whereHas('currentStep', fn ($s) => $s->whereIn('code', [
                               'FINALISEE', 'REJETEE', 'ANNULEE',
                           ]));
                    });
                });

            } elseif ($folder === 'urgent') {
                $q->where(function ($q2) {
                    $q2->where(function ($q3) {
                        $q3->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                           ->where(function ($q4) {
                               $q4->whereNotIn('data->rdv_statut', RencontreStatutEnum::terminalValues())
                                  ->orWhereNull('data->rdv_statut');
                           });
                    })->orWhere(function ($q3) {
                        $q3->where('type', '!=', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                           ->whereHas('currentStep', fn ($s) => $s->whereNotIn('code', [
                               'FINALISEE', 'REJETEE', 'ANNULEE',
                           ]));
                    });
                })->where(function ($q2) {
                    $q2->where('is_urgent', true)
                       ->orWhere('submitted_at', '<=', now()->subDays(30));
                });

            } elseif ($folder === 'rencontre') {
                $q->where('type', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                  ->where(function ($q2) {
                      $q2->whereNotIn('data->rdv_statut', RencontreStatutEnum::terminalValues())
                        ->orWhereNull('data->rdv_statut');
                  })
                  ->where('data->agent_id', $userId);

            } else {
                $q->where('type', '!=', TypeDemandeEnum::DEMANDE_RENCONTRE->value)
                  ->whereHas('currentStep', fn ($s) => $s->whereNotIn('code', [
                      'FINALISEE', 'REJETEE', 'ANNULEE',
                  ]))
                  ->where('categorie', $folder);
            }
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
     * @param  \Illuminate\Database\Eloquent\Builder<\App\Models\Demande>  $baseQuery
     * @return array{pending: int, approved: int, in_progress: int, rejected: int, canceled: int, completed: int}
     */
    private function demandeQueryStats($baseQuery): array
    {
        return [
            'pending'     => (clone $baseQuery)->pending()->count(),
            'approved'    => (clone $baseQuery)->approved()->count(),
            'in_progress' => (clone $baseQuery)->inProgress()->count(),
            'rejected'    => (clone $baseQuery)->rejected()->count(),
            'canceled'    => (clone $baseQuery)->canceled()->count(),
            'completed'   => (clone $baseQuery)->completed()->count(),
        ];
    }
}