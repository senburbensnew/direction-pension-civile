<?php

namespace App\Http\Controllers;

use App\Models\PensionRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Status;

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
        $customMessages = [
            // Certificat de Carrière
            'certificat_carriere.required' => 'Le certificat de carrière est obligatoire.',
            'certificat_carriere.file' => 'Le certificat de carrière doit être un fichier.',
            'certificat_carriere.mimes' => 'Le certificat de carrière doit être au format PDF.',
            'certificat_carriere.max' => 'Le certificat de carrière ne doit pas dépasser 2 Mo.',

            // Copie du Moniteur
            'copie_moniteur.required' => 'La copie du Moniteur est obligatoire.',
            'copie_moniteur.file' => 'La copie du Moniteur doit être un fichier.',
            'copie_moniteur.mimes' => 'La copie du Moniteur doit être au format PDF.',
            'copie_moniteur.max' => 'La copie du Moniteur ne doit pas dépasser 2 Mo.',

            // Acte de Mariage
            'acte_mariage.required' => "L'acte de mariage est obligatoire.",
            'acte_mariage.file' => "L'acte de mariage doit être un fichier.",
            'acte_mariage.mimes' => "L'acte de mariage doit être au format PDF.",
            'acte_mariage.max' => "L'acte de mariage ne doit pas dépasser 2 Mo.",

            // Acte de Naissance
            'acte_naissance.required' => "L'acte de naissance est obligatoire.",
            'acte_naissance.file' => "L'acte de naissance doit être un fichier.",
            'acte_naissance.mimes' => "L'acte de naissance doit être au format PDF.",
            'acte_naissance.max' => "L'acte de naissance ne doit pas dépasser 2 Mo.",

            // Acte de Divorce
            'acte_divorce.file' => "L'acte de divorce doit être un fichier.",
            'acte_divorce.mimes' => "L'acte de divorce doit être au format PDF.",
            'acte_divorce.max' => "L'acte de divorce ne doit pas dépasser 2 Mo.",

            // Matricule fiscal + CIN
            'matricule_fiscal_cin.required' => 'Le matricule fiscal avec CIN est obligatoire.',
            'matricule_fiscal_cin.file' => 'Le matricule fiscal avec CIN doit être un fichier.',
            'matricule_fiscal_cin.mimes' => 'Le matricule fiscal avec CIN doit être en PDF, JPG ou PNG.',
            'matricule_fiscal_cin.max' => 'Le matricule fiscal avec CIN ne doit pas dépasser 2 Mo.',

            // Photos
            'photos.required' => 'Les photos d\'identité sont obligatoires.',
            'photos.array' => 'Les photos doivent être envoyées sous forme de fichiers.',
            'photos.min' => 'Veuillez télécharger exactement 2 photos.',
            'photos.max' => 'Veuillez télécharger exactement 2 photos.',
            'photos.*.image' => 'Les photos doivent être des images.',
            'photos.*.mimes' => 'Les photos doivent être en format JPEG, PNG ou JPG.',
            'photos.*.max' => 'Chaque photo ne doit pas dépasser 1 Mo.',

            // Certificat Médical
            'certificat_medical.required' => 'Le certificat médical est obligatoire.',
            'certificat_medical.file' => 'Le certificat médical doit être un fichier.',
            'certificat_medical.mimes' => 'Le certificat médical doit être au format PDF.',
            'certificat_medical.max' => 'Le certificat médical ne doit pas dépasser 2 Mo.',

            // Souche de chèque
            'souche_cheque.required' => 'La souche de chèque est obligatoire.',
            'souche_cheque.file' => 'La souche de chèque doit être un fichier.',
            'souche_cheque.mimes' => 'La souche de chèque doit être en PDF, JPG ou PNG.',
            'souche_cheque.max' => 'La souche de chèque ne doit pas dépasser 2 Mo.',
        ];

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
        ], $customMessages);

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

        // System fields
        $validated['status_id'] = Status::getStatusPending()->id;
        $validated['created_by'] = $user->id();

        // Create pension request
        PensionRequest::create([
            'certificat_carriere' => $filePaths['certificat_carriere'],
            'copie_moniteur' => $filePaths['copie_moniteur'],
            'acte_mariage' => $filePaths['acte_mariage'],
            'acte_naissance' => $filePaths['acte_naissance'],
            'acte_divorce' => $filePaths['acte_divorce'] ?? null,
            'matricule_fiscal_cin' => $filePaths['matricule_fiscal_cin'],
            'photos' => json_encode($photos),
            'certificat_medical' => $filePaths['certificat_medical'],
            'souche_cheque' => $filePaths['souche_cheque'],
            'status_id' => $validated['status_id'],
            'created_by' => $validated['created_by']
        ]);

        return redirect()->route('fonctionnaire.dashboard')
            ->with('success', 'Votre demande de pension a été soumise avec succès!');
    }
}

