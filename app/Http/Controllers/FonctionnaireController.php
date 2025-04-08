<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FonctionnaireController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->can('viewFonctionnaireMenu')) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }
    

    public function demandeEtatCarriere()
    {
        return view('fonctionnaire.career-state-form');
    }

    public function retirementSimulation(){
        return view('fonctionnaire.simulation-retraite-form'); 
    }

    public function demandePension(){
        return view('fonctionnaire.demande_pension'); 
    }

    public function showPensionStandardForm()
    {
        return view('fonctionnaire.demande-pension-standard');
    }

    public function processPensionStandard(Request $request)
    {
        // Handle standard pension form submission
        dd($request->all());
        // Validate the request
        $validated = $request->validate([
            'certificat_carriere' => 'required|file|mimes:pdf|max:2048',
            'copie_moniteur' => 'required|file|mimes:pdf|max:2048',
            'acte_mariage' => 'required|file|mimes:pdf|max:2048',
            'acte_naissance' => 'required|file|mimes:pdf|max:2048',
            'acte_divorce' => 'nullable|file|mimes:pdf|max:2048',
            'matricule_fiscal_cin' => 'required|file|mimes:pdf,jpg,png|max:2048',
            'photos' => 'required|array|min:2|max:2',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:1024',
            'certificat_medical' => 'required|file|mimes:pdf|max:2048',
            'souche_cheque' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ]);

        // Get authenticated user
        $user = Auth::user();

        // Process file uploads
        $filePaths = [];
        foreach ([
            'certificat_carriere',
            'copie_moniteur',
            'acte_mariage',
            'acte_naissance',
            'acte_divorce',
            'matricule_fiscal_cin',
            'certificat_medical',
            'souche_cheque',
        ] as $field) {
            if ($request->hasFile($field)) {
                $filePaths[$field] = $request->file($field)->store("pension-docs/{$user->id}", 'public');
            }
        }

        // Process photos (multiple)
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photos[] = $photo->store("pension-docs/{$user->id}/photos", 'public');
            }
        }

        // Create pension request
        PensionRequest::create([
            'user_id' => $user->id,
            'certificat_carriere' => $filePaths['certificat_carriere'],
            'copie_moniteur' => $filePaths['copie_moniteur'],
            'acte_mariage' => $filePaths['acte_mariage'],
            'acte_naissance' => $filePaths['acte_naissance'],
            'acte_divorce' => $filePaths['acte_divorce'] ?? null,
            'matricule_fiscal_cin' => $filePaths['matricule_fiscal_cin'],
            'photos' => json_encode($photos),
            'certificat_medical' => $filePaths['certificat_medical'],
            'souche_cheque' => $filePaths['souche_cheque'],
            'status' => 'pending',
        ]);

        return redirect()->route('fonctionnaire.dashboard')
            ->with('success', 'Votre demande de pension a été soumise avec succès!');
    }
}

