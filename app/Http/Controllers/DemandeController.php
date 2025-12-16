<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use Illuminate\Http\Request;

class DemandeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $demandes = Demande::where('user_id', $user->id)
        ->orderBy('created_at','desc')
        ->paginate(15);

        return view('demandes.index', compact('demandes'));
    }

    public function show(Demande $demande)
    {
        $this->authorize('view', $demande);

        $demande->load('histories.changer');

        return view('demandes.show', compact('demande'));
    }
}
