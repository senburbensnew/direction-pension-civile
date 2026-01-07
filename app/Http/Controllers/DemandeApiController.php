<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Demande;

class DemandeApiController extends Controller
{
    public function progress(Demande $demande)
    {
        $this->authorize('view', $demande);

        $statuses = [
            'reçue',
            'en analyse',
            'en attente de documents',
            'validée',
            'refusée',
            'traitée',
            'clôturée',
        ];

        $history = $demande->histories()
            ->with('changer')
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'dossier' => $demande->only([
                'id',
                'numero_dossier',
                'type',
                'etat',
                'categorie',
            ]),
            'statuses' => $statuses,
            'history'  => $history,
        ]);
    }
}
