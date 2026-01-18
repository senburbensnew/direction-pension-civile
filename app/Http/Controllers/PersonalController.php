<?php


namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\Service;
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

    public function showRequestForAuthenticatedUser(Request $request, int $id)
    {
        $demande = Demande::where('created_by', auth()->id())
            ->findOrFail($id);

        $requestHistories = DemandeHistory::where('demande_id', $demande->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('personal.request-details', [
            'from' => 'dashboard',
            'request' => $demande,
            'requestHistories' => $requestHistories,
        ]);
    }

    public function corbeille()
    {
        // PENSIONNAIRE
        $bankTransferRequestCounts = Demande::where('type', 'DEMANDE_VIREMENT_BANCAIRE')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $certificateRequestCounts = Demande::where('type', 'DEMANDE_ATTESTATION')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $checkTransferRequestCounts = Demande::where('type', 'DEMANDE_TRANSFERT_CHEQUE')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $paymentStopRequestCounts = Demande::where('type', 'DEMANDE_ARRET_PAIEMENT')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $reinstateRequestCounts = Demande::where('type', 'DEMANDE_REINSERTION')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $transferStopRequestCounts = Demande::where('type', 'DEMANDE_ARRET_VIREMENT')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $existenceProofRequestCounts = Demande::where('type', 'DEMANDE_PREUVE_EXISTENCE')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $reversionaryPensionRequestCounts = Demande::where('type', 'DEMANDE_PENSION_REVERSION')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        // FONCTIONNAIRE
        $carreerStateRequestCounts = Demande::where('type', 'DEMANDE_ETAT_CARRIERE')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        $pensionRequestCounts = Demande::where('type', 'DEMANDE_PENSION')
            ->where('current_service_id', auth()->user()->service?->id)
            ->count();

        // INSTITUTION
        $adhesionRequestCounts = Demande::where('type', 'DEMANDE_ADHESION')
            ->where('current_service_id', auth()->user()->service?->id)
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

        return view('personal.corbeille', compact( 'stats'));
    }

    public function requestsDashboardCorbeille(Request $request)
    {
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
            ->where('current_service_id', auth()->user()->service?->id);

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
        $requestModel = Demande::findOrFail($id);
        $services = Service::all();

        $requestHistories = DemandeHistory::where('demande_id', $requestModel->id)
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('personal.request-details', [
            'from' => 'cart',
            'request' => $requestModel,
            'requestHistories' => $requestHistories,
            'services' => $services
        ]);
    }
}
