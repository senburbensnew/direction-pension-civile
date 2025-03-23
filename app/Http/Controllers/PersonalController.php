<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankTransferRequests;

class PersonalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('personal.dashboard'); // Make sure you have resources/views/personal/index.blade.php
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('personal.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validation logic here
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return view('personal.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('personal.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Update logic here
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Delete logic here
    }


    // Afficher le tableau de bord des demandes
public function dashboard()
{
    $bankTransferRequests = BankTransferRequests::where('created_by', auth()->id())
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        $stats = [
            'pending' => BankTransferRequests::forUser()->pending()->count(),
            'approved' => BankTransferRequests::forUser()->approved()->count(),
            'in_progress' => BankTransferRequests::forUser()->inProgress()->count(),
            'rejected' => BankTransferRequests::forUser()->rejected()->count(),
            'completed' => BankTransferRequests::forUser()->completed()->count(),
        ];

    return view('personal.index', compact('bankTransferRequests', 'stats'));
}

// Afficher les détails d'une demande
public function showRequest($id)
{
    $request = BankTransferRequests::where('created_by', auth()->id())->findOrFail($id);
    return view('personal.request-details', compact('request'));
}

// Mettre à jour une demande
public function updateRequest(Request $request, $id)
{
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

// Annuler une demande
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
