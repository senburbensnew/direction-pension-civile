<?php


namespace App\Http\Controllers;

use App\Enums\TypeDemandeEnum;
use App\Models\CheckTransferRequests;
use App\Models\Demande;
use App\Models\DemandeActivityLog;
use App\Models\DemandeDocument;
use App\Models\DemandeHistory;
use App\Models\DemandeMessage;
use App\Models\ExistenceProofRequest;
use App\Models\PaymentStopRequests;
use App\Models\PensionRequest;
use App\Models\Service;
use App\Models\Status;
use App\Models\TypeDemande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonalController extends Controller
{
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

        // Fallback : trouver le service correspondant au rôle
        $roleToService = [
            'direction'                  => Service::DIRECTION,
            'secretariat'                => Service::SECRETARIAT,
            'service_liquidation'        => Service::LIQUIDATION,
            'service_controle_placement' => Service::CONTROLE_PLACEMENT,
            'service_comptabilite'       => Service::COMPTABILITE,
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
        return redirect()->route('personal.dashboard');
    }

    public function dashboard(Request $request)
    {
        $query = Demande::where('created_by', auth()->id());

        // Filter by status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        // Filter by type de demande
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $demandes = $query->latest()->get();

        $statuses = Status::orderBy('libelle')->get();
        $typesDemandes = TypeDemande::orderBy('label')->get();

        return view('personal.index2', compact(
            'demandes',
            'statuses',
            'typesDemandes'
        ));
    }

    public function requestsDashboard(Request $request){
        // dd($request->all());
        $requestType = $request['request_type'];
        $requests = [];
        $stats = [
            'pending' => 0,
            'approved' => 0,
            'in_progress' => 0,
            'rejected' => 0,
            'completed' => 0,
            'canceled' => 0,
        ];
        $type = '';

        switch($requestType){
            case 'bankTransferRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Demande de virement';
                break;
            case 'certificateRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_ATTESTATION->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Demande d\'attestation';
                break;
            case 'checkTransferRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Demande de transfert de chèques';
                break;
            case 'paymentStopRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Demande d\'arrêt de paiement';
                break;
            case 'reinstateRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_REINSERTION->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Demande de réinsertion';
                break;
            case 'transferStopRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Demande d\'arrêt de virement';
                break;
            case 'existenceProofRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Preuve d\'existence';
                break;
            case 'reversionaryPensionRequest' :
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];
                $type = 'Demande de pension de réversion';
                break;
            case 'careerStateRequest':
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];

                $type = 'Demande d\'état de carrière';
                break;
            case 'pensionRequest':
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_PENSION->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];

                $type = 'Demande de pension';
                break;
            case 'adhesionRequest':
                $baseQuery = Demande::forUser()
                    ->ofType(TypeDemandeEnum::DEMANDE_ADHESION->value);

                $requests = $baseQuery->latest()->paginate(10);

                $stats = [
                    'pending'     => (clone $baseQuery)->pending()->count(),
                    'approved'    => (clone $baseQuery)->approved()->count(),
                    'in_progress' => (clone $baseQuery)->inProgress()->count(),
                    'rejected'    => (clone $baseQuery)->rejected()->count(),
                    'canceled'    => (clone $baseQuery)->canceled()->count(),
                    'completed'   => (clone $baseQuery)->completed()->count(),
                ];

                $type = 'Demande d\'adhésion';
                break;
        }

        return view('personal.requests', compact('requests', 'stats', 'requestType','type')); 
    }

    public function showRequestForAuthenticatedUser(Request $request, int $id)
    {
        $demande = Demande::where('created_by', auth()->id())
            ->findOrFail($id);

        $requestHistories = DemandeHistory::where('demande_id', $demande->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $messages = $demande->messages()->with('sender')->get();

        // Mark unread service messages as read
        $messages->each(function ($msg) {
            if ($msg->isFromService() && is_null($msg->read_at)) {
                $msg->markAsRead();
            }
        });

        return view('personal.request-details', [
            'from'             => 'dashboard',
            'request'          => $demande,
            'requestHistories' => $requestHistories,
            'activityLogs'     => collect(),
            'messages'         => $messages,
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
            $soumiseStatusId = Status::where('code', 'SOUMISE')->value('id');

            DemandeMessage::create([
                'demande_id' => $demande->id,
                'sender_id'  => auth()->id(),
                'body'       => $request->message,
            ]);

            if ($request->hasFile('documents')) {
                foreach ($request->file('documents') as $file) {
                    $path = $file->store("demandes/{$demande->id}/complements", 'public');
                    DemandeDocument::create([
                        'demande_id'    => $demande->id,
                        'type'          => 'complement',
                        'label'         => 'Complément usager',
                        'disk'          => 'public',
                        'path'          => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type'     => $file->getClientMimeType(),
                        'size'          => $file->getSize(),
                    ]);
                }
            }

            $demande->update(['status_id' => $soumiseStatusId]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => 'SOUMISE',
                'commentaire' => 'Réponse de l\'usager : ' . $request->message,
                'changed_by'  => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Votre réponse a été envoyée. Le dossier est retourné en traitement.');
    }

    public function corbeille()
    {
        $serviceId = $this->resolveServiceId();

        // PENSIONNAIRE
        $bankTransferRequestCounts = Demande::where('type', 'DEMANDE_VIREMENT_BANCAIRE')
            ->where('current_service_id', $serviceId)
            ->count();

        $certificateRequestCounts = Demande::where('type', 'DEMANDE_ATTESTATION')
            ->where('current_service_id', $serviceId)
            ->count();

        $checkTransferRequestCounts = Demande::where('type', 'DEMANDE_TRANSFERT_CHEQUE')
            ->where('current_service_id', $serviceId)
            ->count();

        $paymentStopRequestCounts = Demande::where('type', 'DEMANDE_ARRET_PAIEMENT')
            ->where('current_service_id', $serviceId)
            ->count();

        $reinstateRequestCounts = Demande::where('type', 'DEMANDE_REINSERTION')
            ->where('current_service_id', $serviceId)
            ->count();

        $transferStopRequestCounts = Demande::where('type', 'DEMANDE_ARRET_VIREMENT')
            ->where('current_service_id', $serviceId)
            ->count();

        $existenceProofRequestCounts = Demande::where('type', 'DEMANDE_PREUVE_EXISTENCE')
            ->where('current_service_id', $serviceId)
            ->count();

        $reversionaryPensionRequestCounts = Demande::where('type', 'DEMANDE_PENSION_REVERSION')
            ->where('current_service_id', $serviceId)
            ->count();

        // FONCTIONNAIRE
        $carreerStateRequestCounts = Demande::where('type', 'DEMANDE_ETAT_CARRIERE')
            ->where('current_service_id', $serviceId)
            ->count();

        $pensionRequestCounts = Demande::where('type', 'DEMANDE_PENSION')
            ->where('current_service_id', $serviceId)
            ->count();

        // INSTITUTION
        $adhesionRequestCounts = Demande::where('type', 'DEMANDE_ADHESION')
            ->where('current_service_id', $serviceId)
            ->count();


        $stats = [
            'pensionnaire' => [
                [
                    'label' => 'Demande de virement',
                    'count' => $bankTransferRequestCounts,
                    'type' => 'bankTransferRequest'
                ],
                [
                    'label' => 'Demande d\'attestation',
                    'count' => $certificateRequestCounts,
                    'type' => 'certificateRequest'
                ],
                [
                    'label' => 'Demande de transfert de chèques',
                    'count' => $checkTransferRequestCounts,
                    'type' => 'checkTransferRequest'
                ],
                [
                    'label' => 'Demande d\'arrêt de paiement',
                    'count' => $paymentStopRequestCounts,
                    'type' => 'paymentStopRequest'
                ],
                [
                    'label' => 'Demande de réinsertion',
                    'count' => $reinstateRequestCounts,
                    'type' => 'reinstateRequest'
                ],
                [
                    'label' => 'Demande d\'arrêt de virement',
                    'count' => $transferStopRequestCounts,
                    'type' => 'transferStopRequest'
                ],
                [
                    'label' => 'Preuve d\'existence',
                    'count' => $existenceProofRequestCounts,
                    'type' => 'existenceProofRequest'
                ],
            ],
            'fonctionnaire' => [
                [
                    'label' => 'Demande d\'état de carrière',
                    'count' => $carreerStateRequestCounts,
                    'type' => 'careerStateRequest'
                ],
                [
                    'label' => 'Demande de pension',
                    'count' => $pensionRequestCounts,
                    'type' => 'pensionRequest'
                ],
            ],
            'institution' => [
                [
                    'label' => 'Demande d\'adhésion',
                    'count' => $adhesionRequestCounts,
                    'type' => 'adhesionRequest'
                ],
                [
                    'label' => 'Demande de pension',
                    'count' => $pensionRequestCounts,
                    'type' => 'pensionRequest'
                ],
                [
                    'label' => 'Demande de pension de réversion',
                    'count' => $reversionaryPensionRequestCounts,
                    'type' => 'reversionaryPensionRequest'
                ],
            ],
        ];

        $serviceId = $this->resolveServiceId();

        $folderStats = [
            ['key' => 'pension',         'label' => 'Demandes de pension',    'icon' => 'fa-file-alt',   'color' => 'blue'],
            ['key' => 'urgent',          'label' => 'Dossiers urgents',       'icon' => 'fa-exclamation-triangle', 'color' => 'red'],
            ['key' => 'suivi',           'label' => 'Suivi de dossiers',      'icon' => 'fa-tasks',      'color' => 'yellow'],
            ['key' => 'correspondances', 'label' => 'Correspondances',        'icon' => 'fa-envelope',   'color' => 'purple'],
            ['key' => 'rencontre',       'label' => 'Demandes de rencontre',  'icon' => 'fa-calendar',   'color' => 'green'],
        ];

        foreach ($folderStats as &$folder) {
            $folder['count'] = Demande::where('folder', $folder['key'])
                ->where('current_service_id', $serviceId)
                ->count();
        }
        unset($folder);

        return view('personal.corbeille', compact('stats', 'folderStats'));
    }

    public function requestsDashboardCorbeille(Request $request)
    {
        $serviceId   = $this->resolveServiceId();
        $requestType = $request->input('request_type');

        $map = [
            'bankTransferRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE,
                'label' => 'Demande de virement',
            ],
            'certificateRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_ATTESTATION,
                'label' => 'Demande d\'attestation',
            ],
            'checkTransferRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE,
                'label' => 'Demande de transfert de chèques',
            ],
            'paymentStopRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT,
                'label' => 'Demande d\'arrêt de paiement',
            ],
            'reinstateRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_REINSERTION,
                'label' => 'Demande de réinsertion',
            ],
            'transferStopRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_ARRET_VIREMENT,
                'label' => 'Demande d\'arrêt de virement',
            ],
            'existenceProofRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE,
                'label' => 'Preuve d\'existence',
            ],
            'reversionaryPensionRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_PENSION_REVERSION,
                'label' => 'Demande de pension de réversion',
            ],
            'careerStateRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_ETAT_CARRIERE,
                'label' => 'Demande d\'état de carrière',
            ],
            'pensionRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_PENSION,
                'label' => 'Demande de pension',
            ],
            'adhesionRequest' => [
                'enum' => TypeDemandeEnum::DEMANDE_ADHESION,
                'label' => 'Demande d\'adhésion',
            ],
        ];

        abort_unless(isset($map[$requestType]), 404);

        $config = $map[$requestType];

        $baseQuery = Demande::ofType($config['enum']->value)
            ->where('current_service_id', $serviceId);

        $requests = $baseQuery->latest()->paginate(10);

        $stats = [
            'pending'     => (clone $baseQuery)->pending()->count(),
            'approved'    => (clone $baseQuery)->approved()->count(),
            'in_progress' => (clone $baseQuery)->inProgress()->count(),
            'rejected'    => (clone $baseQuery)->rejected()->count(),
            'canceled'    => (clone $baseQuery)->canceled()->count(),
            'completed'   => (clone $baseQuery)->completed()->count(),
        ];

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
        $requestModel = Demande::with(['status', 'activityLogs.user'])->findOrFail($id);
        $services = Service::all();

        // Tracer la consultation
        DemandeActivityLog::create([
            'demande_id' => $requestModel->id,
            'user_id'    => auth()->id(),
            'action'     => 'viewed',
        ]);

        $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        $activityLogs = $requestModel->activityLogs()
            ->with('user')
            ->latest()
            ->take(20)
            ->get();

        $messages = $requestModel->messages()->with('sender')->get();

        // Mark unread user messages as read
        $messages->each(function ($msg) {
            if (!$msg->isFromService() && is_null($msg->read_at)) {
                $msg->markAsRead();
            }
        });

        return view('personal.request-details', [
            'from'             => 'cart',
            'request'          => $requestModel,
            'requestHistories' => $requestHistories,
            'services'         => $services,
            'activityLogs'     => $activityLogs,
            'messages'         => $messages,
        ]);
    }

    public function corbeilleByFolder(Request $request)
    {
        $serviceId = $this->resolveServiceId();
        $folder    = $request->input('folder');

        $folders = [
            'pension'         => 'Demandes de pension',
            'urgent'          => 'Dossiers urgents',
            'suivi'           => 'Suivi de dossiers',
            'correspondances' => 'Correspondances',
            'rencontre'       => 'Demandes de rencontre',
        ];

        abort_unless(isset($folders[$folder]), 404);

        $baseQuery = Demande::where('folder', $folder)
            ->where('current_service_id', $serviceId);

        $requests = $baseQuery->latest()->paginate(10);
        $type     = $folders[$folder];

        $statusCodes = [
            'pending'     => Status::STATUS_PENDING,
            'in_progress' => Status::STATUS_IN_PROGRESS,
            'rejected'    => Status::STATUS_REJECTED,
            'canceled'    => Status::STATUS_CANCELED,
            'approved'    => Status::STATUS_APPROVED,
            'completed'   => Status::STATUS_COMPLETED,
        ];

        $stats = [];
        foreach ($statusCodes as $key => $code) {
            $stats[$key] = Demande::where('folder', $folder)
                ->where('current_service_id', $serviceId)
                ->whereHas('status', fn($q) => $q->where('code', $code))
                ->count();
        }

        return view('personal.dashboard-corbeille', compact('requests', 'type', 'folder', 'stats'));
    }
}
