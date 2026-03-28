<?php

namespace App\Http\Controllers;

use App\Models\Demande;
use App\Models\DemandeActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\View;

class DemandePdfController extends Controller
{
    public function download(Demande $demande)
    {
        abort_if(
            !$demande->isAnnotated(),
            403,
            'Le dossier doit être annoté par la Direction avant d\'être téléchargé.'
        );

        $demande->load(['user', 'status', 'documents', 'annotatedBy', 'service']);

        // Vue spécifique au type si elle existe, sinon générique
        $viewName = 'demandes.pdf.' . strtolower($demande->type);
        $view     = View::exists($viewName) ? $viewName : 'demandes.pdf.generic';

        $pdf = Pdf::loadView($view, ['demande' => $demande])
            ->setPaper('a4', 'portrait')
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('isHtml5ParserEnabled', true);

        // Logger l'activité
        DemandeActivityLog::create([
            'demande_id' => $demande->id,
            'user_id'    => auth()->id(),
            'action'     => 'downloaded',
            'metadata'   => ['filename' => $demande->code . '.pdf'],
        ]);

        return $pdf->download($demande->code . '.pdf');
    }
}
