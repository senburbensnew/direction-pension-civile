<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Demande;
use App\Models\DemandeHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DemandeManagementController extends Controller
{
    public function index()
    {
        // filtrer par categorie/type/etat
        $demandes = Demande::orderBy('created_at','desc')->paginate(50);
        return view('demandes.admin.index', compact('demandes'));
    }


    public function edit(Demande $demande)
    {
        return view('demandes.admin.edit', compact('demande'));
    }


    public function updateStatus(Request $request, Demande $demande)
    {
        $request->validate([
        'etat' => 'required|string',
        'commentaire' => 'nullable|string',
        ]);

        DB::transaction(function() use ($demande, $request) {
            $demande->update(['etat' => $request->etat]);
            DemandeHistory::create([
            'demande_id' => $demande->id,
            'etat' => $request->etat,
            'commentaire' => $request->commentaire,
            'changed_by' => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'État mis à jour');
    }
}
