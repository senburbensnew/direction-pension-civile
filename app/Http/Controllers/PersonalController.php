<?php


namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;
use App\Enums\TypeDemandeEnum;
use App\Models\DemandeHistory;
use App\Models\PensionRequest;
use App\Models\PaymentStopRequests;
use App\Models\CheckTransferRequests;
use App\Models\ExistenceProofRequest;

class PersonalController extends Controller
{
    public function index()
    {
        return redirect()->route('personal.dashboard');
    }

    public function dashboard()
    {
        // PENSIONNAIRE
        $bankTransferRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_VIREMENT_BANCAIRE')
            ->count();

        $certificateRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_ATTESTATION')
            ->count();

        $checkTransferRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_TRANSFERT_CHEQUE')
            ->count();

        $paymentStopRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_ARRET_PAIEMENT')
            ->count();

        $reinstateRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_REINSERTION')
            ->count();

        $transferStopRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_ARRET_VIREMENT')
            ->count();

        $existenceProofRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_PREUVE_EXISTENCE')
            ->count();

        // FONCTIONNAIRE
        $carreerStateRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_ETAT_CARRIERE')
            ->count();

        $pensionRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_PENSION')
            ->count();

        // INSTITUTION
        $adhesionRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_ADHESION')
            ->count();


        // PENSIONNAIRE ET FONCTIONNAIRE
        $reversionaryPensionRequestCounts = Demande::where('created_by', auth()->id())
            ->where('type', 'DEMANDE_PENSION_REVERSION')
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
                    'label' => 'Demande d\'arret de paiement',
                    'count' => $paymentStopRequestCounts,
                    'type' => 'paymentStopRequest'
                ],
                [
                    'label' => 'Demande de reinsertion',
                    'count' => $reinstateRequestCounts,
                    'type' => 'reinstateRequest'
                ],
                [
                    'label' => 'Demande d\'arret de virement',
                    'count' => $transferStopRequestCounts,
                    'type' => 'transferStopRequest'
                ],
                [
                    'label' => 'Preuve d\'existence',
                    'count' => $existenceProofRequestCounts,
                    'type' => 'existenceProofRequest'
                ],
                [
                    'label' => 'Demande de pension de réversion',
                    'count' => $reversionaryPensionRequestCounts,
                    'type' => 'reversionaryPensionRequest'
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
                [
                    'label' => 'Demande de pension de réversion',
                    'count' => $reversionaryPensionRequestCounts,
                    'type' => 'reversionaryPensionRequest'
                ],
            ],
            'institution' => [
                [
                    'label' => 'Demande d\'adhésion',
                    'count' => $adhesionRequestCounts,
                    'type' => 'adhesionRequest'
                ],
            ],
        ];

        return view('personal.index', compact( 'stats'));
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

/*     public function showRequest(Request $request, $id)
    {
        $requestType = $request->query('requestType');
        $requestHistories = [];
        $requestModel = null;

        switch($requestType) {
            case 'bankTransferRequest':
                $requestModel = Demande::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
                                                    ->orderBy('id', 'desc')
                                                    ->paginate(10);
                break;
            case 'certificateRequest':
                $requestModel = Demande::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
                                                    ->orderBy('id', 'desc')
                                                    ->paginate(10);
                break;
            case 'checkTransferRequest':
                $requestModel = Demande::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)->where('request_type', 'CHECK_TRANSFER_REQUEST')
                                                    ->orderBy('event_date', 'desc')
                                                    ->paginate(10);
                break;
            case 'paymentStopRequest':
                $requestModel = Demande::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)->where('request_type', 'PAYMENT_STOP_REQUEST')
                                                    ->orderBy('event_date', 'desc')
                                                    ->paginate(10);
                break;
            case 'existenceProofRequest':
                $requestModel = Demande::with('dependants')->where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
                    ->where('request_type', 'EXISTENCE_PROOF_REQUEST')
                    ->orderBy('event_date', 'desc')
                    ->paginate(10);
                break;
            case 'pensionRequest':
                $requestModel = Demande::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
                    ->where('request_type', 'PENSION_REQUEST')
                    ->orderBy('event_date', 'desc')
                    ->paginate(10);
                break;
        }

        return view('personal.request-details', [
            'request' => $requestModel,
            'requestType' => $requestType,
            'requestHistories' => $requestHistories
        ]);
    }
 */

    public function showRequest(Request $request, $id)
    {
        $REQUEST_CONFIG = [
            // Pensionnaire
            'bankTransferRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'certificateRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'checkTransferRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'paymentStopRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'reinstateRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'transferStopRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'existenceProofRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'reversionaryPensionRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            // Fonctionnaire
            'careerStateRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            'pensionRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ],
            // Institution
            'adhesionRequest' => [
                'type' => null,
                'order' => 'id',
                'with' => [],
            ]
        ];

        $requestType = $request->query('requestType');

        abort_unless(
            isset($REQUEST_CONFIG[$requestType]),
            404,
            'Invalid request type'
        );

        $config = $REQUEST_CONFIG[$requestType];

        $requestModel = Demande::with($config['with'])
            ->where('created_by', auth()->id())
            ->findOrFail($id);

        $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
            ->when($config['type'], fn ($q) =>
                $q->where('request_type', $config['type'])
            )
            ->orderBy($config['order'], 'desc')
            ->paginate(10);

        return view('personal.request-details', [
            'request' => $requestModel,
            'requestType' => $requestType,
            'requestHistories' => $requestHistories,
        ]);
    }

    public function corbeille()
    {
        // PENSIONNAIRE
        $bankTransferRequestCounts = Demande::where('type', 'DEMANDE_VIREMENT_BANCAIRE')
            ->count();

        $certificateRequestCounts = Demande::where('type', 'DEMANDE_ATTESTATION')
            ->count();

        $checkTransferRequestCounts = Demande::where('type', 'DEMANDE_TRANSFERT_CHEQUE')
            ->count();

        $paymentStopRequestCounts = Demande::where('type', 'DEMANDE_ARRET_PAIEMENT')
            ->count();

        $reinstateRequestCounts = Demande::where('type', 'DEMANDE_REINSERTION')
            ->count();

        $transferStopRequestCounts = Demande::where('type', 'DEMANDE_ARRET_VIREMENT')
            ->count();

        $existenceProofRequestCounts = Demande::where('type', 'DEMANDE_PREUVE_EXISTENCE')
            ->count();

        $reversionaryPensionRequestCounts = Demande::where('type', 'DEMANDE_PENSION_REVERSION')
            ->count();

        // FONCTIONNAIRE
        $carreerStateRequestCounts = Demande::where('type', 'DEMANDE_ETAT_CARRIERE')
            ->count();

        $pensionRequestCounts = Demande::where('type', 'DEMANDE_PENSION')
            ->count();

        // INSTITUTION
        $adhesionRequestCounts = Demande::where('type', 'DEMANDE_ADHESION')
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
                    'label' => 'Demande d\'arret de paiement',
                    'count' => $paymentStopRequestCounts,
                    'type' => 'paymentStopRequest'
                ],
                [
                    'label' => 'Demande de reinsertion',
                    'count' => $reinstateRequestCounts,
                    'type' => 'reinstateRequest'
                ],
                [
                    'label' => 'Demande d\'arret de virement',
                    'count' => $transferStopRequestCounts,
                    'type' => 'transferStopRequest'
                ],
                [
                    'label' => 'Preuve d\'existence',
                    'count' => $existenceProofRequestCounts,
                    'type' => 'existenceProofRequest'
                ],
                [
                    'label' => 'Demande de pension de reversion',
                    'count' => $reversionaryPensionRequestCounts,
                    'type' => 'reversionaryPensionRequest'
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
                ]
            ],
            'institution' => [
                [
                    'label' => 'Demande d\'adhésion',
                    'count' => $adhesionRequestCounts,
                    'type' => 'adhesionRequest'
                ],
            ],
        ];

        return view('personal.corbeille', compact( 'stats'));
    }

    public function requestsDashboardCorbeille(Request $request){
        //dd($request->all());
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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_ATTESTATION->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_REINSERTION->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_PENSION->value);

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
                $baseQuery = Demande::ofType(TypeDemandeEnum::DEMANDE_ADHESION->value);

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

        return view('personal.dashboard-corbeille', compact('requests', 'stats', 'requestType','type')); 
    }
}
