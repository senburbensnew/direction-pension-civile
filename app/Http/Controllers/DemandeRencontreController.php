<?php

namespace App\Http\Controllers;

use App\Enums\RencontreStatutEnum;
use App\Enums\TypeDemandeEnum;
use App\Helpers\CodeGeneratorService;
use App\Models\Demande;
use App\Models\DemandeHistory;
use App\Models\DirectionDepartementale;
use App\Models\Service;
use App\Models\User;
use App\Rules\Telephone;
use App\Services\RencontreAvailabilityService;
use App\Services\RencontreMotifService;
use App\Services\RencontreVisioService;
use App\Services\RencontreWorkflowService;
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
    ) {
    }

    /**
     * Documents à préparer.
     */
    public const DOCUMENTS_A_PREPARER = [
        'physique' => [
            'Carte de pension (photo jointe au dossier)',
            'Pièce d’identité (CIN, NIF ou passeport)',
            'Tout document relatif au motif du rendez-vous',
        ],

        'visio' => [
            'Carte de pension (photo jointe au dossier)',
            'Pièce d’identité à présenter à l’écran',
            'Tout document relatif au motif du rendez-vous',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | FORMULAIRE
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401);

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

        return [
            'reservedSlots' => Demande::fullyBookedRencontreSlots(),

            'rdvActif' => $this->activeAppointment($user),

            'appointments' => Demande::query()
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

            'motifs' => $this->motifsService->motifs(),

            'motifServices' => $this->motifsService
                ->motifServiceLabels($user),

            'allowedTimes' => $this->availability
                ->allowedTimes(),

            'slotConfig' => [
                'start' => $this->availability
                    ->startTime(),

                'end' => $this->availability
                    ->endTime(),

                'minutes' => $this->availability
                    ->slotMinutes(),

                'daily_capacity' => $this->availability
                    ->dailyCapacity(),
            ],

            'documentsAPreparer' => self::DOCUMENTS_A_PREPARER,

            'identite' => $this->identityDefaults($user),
        ];
    }

    private function activeAppointment(?User $user): ?Demande
    {
        if (!$user) {
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
                    RencontreStatutEnum::VALIDE->value,
                    RencontreStatutEnum::REPORTE->value,
                ]
            )
            ->latest()
            ->first();
    }

    private function identityDefaults(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $nom = $user->lastname ?? '';

        if (!$nom) {
            $nom = $user->name ?? '';
        }

        if (!$nom) {
            $nom = trim(
                ($user->firstname ?? '')
                . ' '
                . ($user->lastname ?? '')
            );
        }

        return [
            'prenom' => $user->firstname
                ?? $user->prenom
                ?? '',

            'nom' => $nom,

            'numero_pension' => $user->numero_pension
                ?? $user->numero_pensionne
                ?? $user->pension_code
                ?? '',

            'telephone' => $user->telephone
                ?? $user->phone
                ?? '',

            'email' => $user->email
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

        $validated = $request->validate([
            'motif' => [
                'required',
                'string',
                'max:255',
            ],

            'modalite' => [
                'required',
                'in:physique,visio',
            ],

            'date_souhaitee' => [
                'required',
                'date',
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
                new Telephone(),
            ],
            'message' => [
                'nullable',
                'string',
                'max:5000',
            ],
            'carte_pension' => [
                'required',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:5120',
            ],
            'confirmation_lu_accepte' => [
                'required',
                'accepted',
            ],
        ]);

        $service = $this->motifsService->resolve(
            $validated['motif'],
            $user
        );

        if (!$service) {
            throw ValidationException::withMessages([
                'motif' =>
                    'Aucun service responsable n’est configuré pour le motif sélectionné.',
            ]);
        }

        if (!$this->availability->isBookableSlot(
            $validated['date_souhaitee'],
            $validated['heure_souhaitee'],
            $service
        )) {
            throw ValidationException::withMessages([
                'heure_souhaitee' =>
                    'Ce créneau n’est pas disponible pour le service « '
                    . $service->nom
                    . ' ». Veuillez choisir un autre créneau.',
            ]);
        }

        $agent = $this->availability->findAvailableAgent(
            $validated['date_souhaitee'],
            $validated['heure_souhaitee'],
            $service
        );

        if (!$agent) {
            throw ValidationException::withMessages([
                'heure_souhaitee' =>
                    'Aucun agent disponible du service « '
                    . $service->nom
                    . ' » pour ce créneau.',
            ]);
        }

        $demande = DB::transaction(
            function () use (
                $validated,
                $user,
                $service,
                $agent
            ) {
                if (
                    !$this->availability->isBookingAgent(
                        $agent,
                        $service
                    )
                ) {
                    throw ValidationException::withMessages([
                        'heure_souhaitee' =>
                            'L’agent disponible ne correspond plus au service responsable.',
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
                        'heure_souhaitee' =>
                            'Ce créneau vient d’être réservé par un autre rendez-vous.',
                    ]);
                }

                if (
                    $this->availability->dailyAppointmentCount(
                        $agent,
                        $validated['date_souhaitee']
                    ) >= $this->availability->dailyCapacity()
                ) {
                    throw ValidationException::withMessages([
                        'date_souhaitee' =>
                            'La capacité journalière de l’agent est atteinte.',
                    ]);
                }

                $code = CodeGeneratorService::generateUniqueRequestCode(
                    'RENCONTRE',
                    'demandes'
                );

                $motifLabel = $this->motifsService->motifLabel($validated['motif']);

                $data = [
                    'motif_key' => $validated['motif'],
                    'motif' => $motifLabel,

                    'modalite' =>
                        $validated['modalite'],

                    'date_souhaitee' =>
                        $validated['date_souhaitee'],

                    'heure_souhaitee' =>
                        Demande::normalizeRencontreTime(
                            $validated['heure_souhaitee']
                        ),

                    'telephone' =>
                        $validated['telephone'] ?? null,

                    'message' =>
                        $validated['message'] ?? null,

                    'lieu_rdv' =>
                        $validated['lieu_rdv'] ?? null,

                    'service_id' =>
                        $service->id,

                    'service_code' =>
                        $service->code,

                    'service_responsable' =>
                        $service->nom,

                    'unite' =>
                        $service->nom,

                    'agent_id' =>
                        $agent->id,

                    'agent_nom' =>
                        $agent->displayName(),

                    'documents_a_preparer' =>
                        self::DOCUMENTS_A_PREPARER[
                            $validated['modalite']
                        ],
                ];

                $demande = Demande::create([
                    'code' => $code,

                    'type' =>
                        TypeDemandeEnum::DEMANDE_RENCONTRE->value,

                    'created_by' =>
                        $user->id,

                    'current_service_id' =>
                        $service->id,

                    'data' =>
                        $data,
                ]);

                return $this->rdvWorkflow->enregistrerSoumission(
                    $demande,
                    $user
                );
            }
        );

        return redirect()
            ->route('demandes.rencontre.create')
            ->with(
                'success',
                'Votre demande de rendez-vous a été enregistrée avec succès.'
            )
            ->with('demande_creee_id', $demande->id);
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
        if (!$user) {
            return false;
        }

        if ($this->rdvWorkflow->statut($demande)->isTerminal()) {
            return false;
        }

        if ((int) $demande->created_by === (int) $user->id) {
            return true;
        }

        if ($user->hasAnyRole(['admin', 'direction'])) {
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
            ->where('visio_token', $token)
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->firstOrFail();

        $user = $request->user();

        abort_unless(
            $user
            && (
                (int) $demande->created_by === (int) $user->id
                || $this->canValidate($user)
            ),
            403
        );

        $data = $demande->data ?? [];

        return view(
            'demandes.rencontre.visio',
            [
                'demande' => $demande,

                'status' =>
                    $this->rdvWorkflow->statut($demande),

                'date' =>
                    $data['date_souhaitee'] ?? null,

                'heure' =>
                    Demande::normalizeRencontreTime(
                        $data['heure_souhaitee'] ?? null
                    ),

                'embedUrl' =>
                    $this->visio->embedUrl($demande),
            ]
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
            $this->canValidate($user)
            || $user?->hasAnyRole([
                'admin',
                'direction',
            ]),
            403
        );

        $query = Demande::query()
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->with([
                'service',
                'currentStep',
                'histories',
                'user',
            ])
            ->latest();

        if ($request->filled('statut')) {
            $query->where(
                'data->rdv_statut',
                $request->string('statut')
            );
        }

        if (!$user->hasAnyRole(['admin', 'direction'])) {
            $query->where('data->agent_id', $user->id);
        }

        $demandes = $query
            ->paginate(20)
            ->withQueryString();

        return view(
            'demandes.rencontre.index',
            [
                'demandes' =>
                    $demandes,

                'agents' =>
                    $this->availability
                        ->allBookingAgents(),

                'canValidate' =>
                    $this->canValidate($user),

                'reminderData' =>
                    $this->reminderData(),

                'statuts' =>
                    RencontreStatutEnum::cases(),
            ]
        );
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
            'histories',
        ]);

        return view(
            'demandes.rencontre.show',
            [
                'demande' =>
                    $demande,

                'service' =>
                    $demande->service,

                'services' =>
                    Service::query()
                        ->orderBy('nom')
                        ->get(),

                'histories' =>
                    $demande->histories
                        ->sortByDesc('created_at'),

                'rdvStatus' =>
                    $this->rdvWorkflow
                        ->statut($demande),

                'statuts' =>
                    RencontreStatutEnum::cases(),

                'confirmation' =>
                    $this->rdvWorkflow
                        ->confirmation($demande),

                'allowedTimes' =>
                    $this->availability
                        ->allowedTimes(),

                'eventLabel' =>
                    fn (string $event) =>
                        $this->rdvWorkflow
                            ->eventLabel($event),
            ]
        );
    }

    private function canAccessDemande(
        Demande $demande,
        ?User $user
    ): bool {
        if (!$user) {
            return false;
        }

        if (
            (int) $demande->created_by === (int) $user->id
        ) {
            return true;
        }

        return $this->canValidate($user)
            || $user->hasAnyRole([
                'admin',
                'direction',
            ]);
    }

    private function canValidate(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $user->hasAnyRole([
            'admin',
            'direction',
            $this->availability->agentRole(),
        ]);
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

        if (!empty($validated['agent_id'])) {
            $agent = User::find(
                $validated['agent_id']
            );

            if (
                !$agent
                || !$this->availability->isBookingAgent(
                    $agent,
                    $service
                )
            ) {
                throw ValidationException::withMessages([
                    'agent_id' =>
                        'L’agent sélectionné n’appartient pas au service responsable.',
                ]);
            }
        }

        if (!$this->availability->isBookableSlot(
            $validated['date_souhaitee'],
            $validated['heure_souhaitee'],
            $service
        )) {
            throw ValidationException::withMessages([
                'heure_souhaitee' =>
                    'Aucun agent du service « '
                    . $service->nom
                    . ' » n’est disponible pour ce créneau.',
            ]);
        }

        $this->rdvWorkflow->proposerCreneau(
            $demande,
            $request->user(),
            $validated,
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

        $this->rdvWorkflow->valider(
            $demande,
            $request->user()
        );

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
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

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
                'refuse_par' =>
                    $request->user()->id,

                'refuse_at' =>
                    now()->toIso8601String(),
            ],
            [
                'action' =>
                    'refus',
            ]
        );

        // Garde-fou : vérifier que le statut a bien été appliqué
        $demande->refresh();

        if ($this->rdvWorkflow->statut($demande) !== RencontreStatutEnum::REFUSE) {
            return back()->with(
                'error',
                'Le refus n’a pas pu être appliqué (statut actuel : '
                . $this->rdvWorkflow->statut($demande)->value . ').'
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
    | REORIENTATION
    |--------------------------------------------------------------------------
    */

    public function reorienter(
        Request $request,
        Demande $demande
    ) {
        $this->authorizeRdvAgent(
            $demande,
            $request->user()
        );

        $validated = $request->validate([
            'service_id' => [
                'required',
                'exists:services,id',
            ],

            'commentaire' => [
                'required',
                'string',
                'max:2000',
            ],
        ]);

        $service = Service::findOrFail(
            $validated['service_id']
        );

        $data = $demande->data ?? [];

        $data['service_id'] =
            $service->id;

        $data['service_code'] =
            $service->code;

        $data['service_responsable'] =
            $service->nom;

        $data['unite'] =
            $service->nom;

        unset(
            $data['agent_id'],
            $data['agent_nom']
        );

        $demande->update([
            'current_service_id' =>
                $service->id,

            'data' =>
                $data,
        ]);

        DemandeHistory::create([
            'demande_id' =>
                $demande->id,

            'event' =>
                'REORIENTED',

            'statut' =>
                $this->rdvWorkflow
                    ->statut($demande)
                    ->value,

            'commentaire' =>
                $validated['commentaire'],

            'changed_by' =>
                $request->user()->id,

            'champs' => [
                'action' =>
                    'reorientation',

                'service_id' =>
                    $service->id,

                'service_code' =>
                    $service->code,
            ],
        ]);

        return back()->with(
            'success',
            'La demande a été réorientée vers le service « '
            . $service->nom
            . ' ».'
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
                'suite' =>
                    $validated['suite'],

                'suite_par' =>
                    $request->user()->id,

                'suite_at' =>
                    now()->toIso8601String(),
            ],
            [
                'action' =>
                    'suite',
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

        $this->rdvWorkflow->apply(
            $demande,
            $this->rdvWorkflow->statut($demande),
            $request->user(),
            'IDENTITY_CONFIRMED',
            'Identité du demandeur confirmée.',
            [
                'identite_confirmee' =>
                    true,

                'identite_confirmee_par' =>
                    $request->user()->id,

                'identite_confirmee_at' =>
                    now()->toIso8601String(),
            ],
            [
                'action' =>
                    'confirmation_identite',
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
            $demande->data['motif']
            ?? null;

        if (!$motif) {
            throw ValidationException::withMessages([
                'motif' =>
                    'Impossible de déterminer le service responsable : le motif est absent.',
            ]);
        }

        $service = $this->motifsService->resolve(
            $motif,
            $demande->user
        );

        if (!$service) {
            throw ValidationException::withMessages([
                'motif' =>
                    'Aucun service responsable n’est configuré pour ce motif.',
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
            $user && $this->canValidate($user),
            403
        );

        if (
            $user->hasAnyRole([
                'admin',
                'direction',
            ])
        ) {
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

    private function reminderData(): array
    {
        return [
            'enabled' => true,
        ];
    }
}