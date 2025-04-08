<?php


namespace App\Http\Controllers;

use App\Models\ExistenceProofRequest;
use App\Models\PensionRequest;
use App\Models\RequestHistory;
use Illuminate\Http\Request;
use App\Models\BankTransferRequests;
use App\Models\PaymentStopRequests;
use App\Models\CheckTransferRequests;

class PersonalController extends Controller
{
    public function index()
    {
        return redirect()->route('personal.dashboard'); // Make sure you have resources/views/personal/index.blade.php
    }

    public function dashboard()
    {
        $bankTransferRequestCounts = BankTransferRequests::where('created_by', auth()->id())->count();
        $checkTransferRequestCounts = CheckTransferRequests::where('created_by', auth()->id())->count();
        $paymentStopRequestCounts = PaymentStopRequests::where('created_by', auth()->id())->count();
        $existenceProofRequestCounts = ExistenceProofRequest::where('created_by', auth()->id())->count();
        $pensionRequestCounts = PensionRequest::where('created_by', auth()->id())->count();

        $stats = [
            'pensionnaire' => [
                [
                    'label' => 'Demande de virement',
                    'count' => $bankTransferRequestCounts,
                    'type' => 'bankTransferRequest'
                ],
                [
                    'label' => 'Demande de transfert de cheques',
                    'count' => $checkTransferRequestCounts,
                    'type' => 'checkTransferRequest'
                ],
                [
                    'label' => 'Demande d\'arret de paiement',
                    'count' => $paymentStopRequestCounts,
                    'type' => 'paymentStopRequest'
                ],
                [
                    'label' => 'Preuve d\'existence',
                    'count' => $existenceProofRequestCounts,
                    'type' => 'existenceProofRequest'
                ],
            ],
            'fonctionnaire' => [
                [
                    'label' => 'Demande de pension',
                    'count' => $pensionRequestCounts,
                    'type' => 'pensionRequest'
                ],
                [
                    'label' => 'Demande d\'etat de carriere',
                    'count' => 0,
                    'type' => 'etatcarriere'
                ],
            ],
            'institution' => [],
        ];

        return view('personal.index', compact( 'stats'));
    }

    public function requestsDashboard(Request $request){
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
                $requests = BankTransferRequests::where('created_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);
                $stats['pending'] = BankTransferRequests::forUser()->pending()->count();
                $stats['approved'] = BankTransferRequests::forUser()->approved()->count();
                $stats['in_progress'] = BankTransferRequests::forUser()->inProgress()->count();
                $stats['rejected'] = BankTransferRequests::forUser()->rejected()->count();
                $stats['completed'] = BankTransferRequests::forUser()->completed()->count();
                $type = 'Demande de virement';
                break;
            case 'checkTransferRequest' :
                $requests = CheckTransferRequests::where('created_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);        
                $stats['pending'] = CheckTransferRequests::forUser()->pending()->count();
                $stats['approved'] = CheckTransferRequests::forUser()->approved()->count();
                $stats['in_progress'] = CheckTransferRequests::forUser()->inProgress()->count();
                $stats['rejected'] = CheckTransferRequests::forUser()->rejected()->count();
                $stats['completed'] = CheckTransferRequests::forUser()->completed()->count();
                $type = 'Demande de transfert de cheques';
                break;
            case 'paymentStopRequest' :
                $requests = PaymentStopRequests::where('created_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);  ;
                $stats['pending'] = PaymentStopRequests::forUser()->pending()->count();
                $stats['approved'] = PaymentStopRequests::forUser()->approved()->count();
                $stats['in_progress'] = PaymentStopRequests::forUser()->inProgress()->count();
                $stats['rejected'] = PaymentStopRequests::forUser()->rejected()->count();
                $stats['completed'] = PaymentStopRequests::forUser()->completed()->count();
                $type = 'Demande d\'arret de paiement';
                break;
            case 'existenceProofRequest' :
                $requests = ExistenceProofRequest::where('created_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);  ;
                $stats['pending'] = ExistenceProofRequest::forUser()->pending()->count();
                $stats['approved'] = ExistenceProofRequest::forUser()->approved()->count();
                $stats['in_progress'] = ExistenceProofRequest::forUser()->inProgress()->count();
                $stats['rejected'] = ExistenceProofRequest::forUser()->rejected()->count();
                $stats['completed'] = ExistenceProofRequest::forUser()->completed()->count();
                $type = 'Preuve d\'existence';
                break;
            case 'pensionRequest' :
                $requests = PensionRequest::where('created_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->paginate(10);  ;
                $stats['pending'] = PensionRequest::forUser()->pending()->count();
                $stats['approved'] = PensionRequest::forUser()->approved()->count();
                $stats['in_progress'] = PensionRequest::forUser()->inProgress()->count();
                $stats['rejected'] = PensionRequest::forUser()->rejected()->count();
                $stats['completed'] = PensionRequest::forUser()->completed()->count();
                $type = 'Demande de pension';
                break;
        }

        return view('personal.requests', compact('requests', 'stats', 'requestType','type')); 
    }

/*     public function showRequest(Request $request, $id)
    {
        $requestType = $request->query('requestType');

        switch($requestType){
            case 'bankTransferRequest' :
                $request = BankTransferRequests::where('created_by', auth()->id())->findOrFail($id);
                break;
            case 'checkTransferRequest' :
                $request = CheckTransferRequests::where('created_by', auth()->id())->findOrFail($id);
                break;
            case 'paymentStopRequest' :
                $request = PaymentStopRequests::where('created_by', auth()->id())->findOrFail($id);
                break;
            case 'existenceProofRequest' :
                $request = ExistenceProofRequest::where('created_by', auth()->id())->findOrFail($id);
                break;
            default :
                $request = BankTransferRequests::where('created_by', auth()->id())->findOrFail($id);
                break;
        }

        return view('personal.request-details', compact('request', 'requestType'));
    } */

    public function showRequest(Request $httpRequest, $id)
    {
        $requestType = $httpRequest->query('requestType');
        $requestHistories = [];
        $requestModel = null;

        switch($requestType) {
            case 'bankTransferRequest':
                $requestModel = BankTransferRequests::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = RequestHistory::where('request_id', $requestModel->id)
                    ->where('request_type', 'BANK_TRANSFER_REQUEST')
                    ->orderBy('event_date', 'desc')
                    ->paginate(10);
                break;
            case 'checkTransferRequest':
                $requestModel = CheckTransferRequests::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = RequestHistory::where('request_id', $requestModel->id)
                    ->where('request_type', 'CHECK_TRANSFER_REQUEST')
                    ->orderBy('event_date', 'desc')
                    ->paginate(10);
                break;
            case 'paymentStopRequest':
                $requestModel = PaymentStopRequests::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = RequestHistory::where('request_id', $requestModel->id)
                    ->where('request_type', 'PAYMENT_STOP_REQUEST')
                    ->orderBy('event_date', 'desc')
                    ->paginate(10);
                break;
            case 'existenceProofRequest':
                $requestModel = ExistenceProofRequest::with('dependants')->where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = RequestHistory::where('request_id', $requestModel->id)
                    ->where('request_type', 'EXISTENCE_PROOF_REQUEST')
                    ->orderBy('event_date', 'desc')
                    ->paginate(10);
                break;
            case 'pensionRequest':
                $requestModel = PensionRequest::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = RequestHistory::where('request_id', $requestModel->id)
                    ->where('request_type', 'PENSION_REQUEST')
                    ->orderBy('event_date', 'desc')
                    ->paginate(10);
                break;
            default:
                $requestModel = BankTransferRequests::where('created_by', auth()->id())->findOrFail($id);
                $requestHistories = RequestHistory::where('request_id', $requestModel->id)
                    ->where('request_type', 'BANK_TRANSFER_REQUEST')
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
    
    public function create()
    {
        return view('personal.create');
    }

    public function updateRequest(Request $request, $id){
        $transferRequest = BankTransferRequests::where('created_by', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'bank_name' => 'sometimes|required|string|max:255',
            'account_number' => 'sometimes|required|digits_between:5,20',
            'account_name' => 'sometimes|required|string|max:255',
            'status' => 'sometimes|required|in:pending,in_progress,completed'
        ]);

        $transferRequest->update($validated);

        return redirect()->back()->with('success', 'Demande mise à jour avec succès');
    }

    public function cancelRequest($id)
    {
        $request = BankTransferRequests::where('created_by', auth()->id())->findOrFail($id);
        
        if($request->status !== 'completed') {
            $request->update(['status' => 'canceled']);
            return redirect()->back()->with('success', 'Demande annulée avec succès');
        }

        return redirect()->back()->with('error', 'Impossible d\'annuler une demande terminée');
    }
}
