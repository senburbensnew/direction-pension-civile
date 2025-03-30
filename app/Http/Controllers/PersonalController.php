<?php


namespace App\Http\Controllers;

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
            ],
            'fonctionnaire' => [],
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
        }

        return view('personal.requests', compact('requests', 'stats', 'requestType','type')); 
    }

    public function showRequest(Request $request, $id)
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
        }

        return view('personal.request-details', compact('request', 'requestType'));
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
