<?php

namespace App\Http\Controllers;

use App\Models\CivilStatus;
use App\Models\Gender;
use App\Models\JoiningRequest;
use Illuminate\Http\Request;

class InstitutionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->can('viewInstitutionMenu')) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }
    

    public function demandeAdhesion()
    {
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        return view('institution.demande_adhesion', compact('genders', 'civilStatuses'));
    }
    
    public function processDemandeAdhesion(Request $request)
    {
        // Validate the request data with French messages
        $validatedData = $request->validate([
            'profile_photo' => [
                'required',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048'
            ],
            'institution' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'mother_lastname' => 'required|string|max:255',
            'mother_firstname' => 'required|string|max:255',
            'birth_place' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'nif' => 'required|string|unique:joining_requests,nif|max:255',
            'ninu' => 'required|string|unique:joining_requests,ninu|max:255',
            'gender_id' => 'required|exists:genders,id',
            'civil_status_id' => 'required|exists:civil_statuses,id',
            'spouse_lastname' => 'nullable|string|max:255',
            'spouse_firstname' => 'nullable|string|max:255',
            'dependents' => 'nullable|array',
            'dependents.*' => 'array:name,relationship,age',
            'entry_date' => 'required|date',
            'current_salary' => 'required|numeric|min:0',
            'previous_jobs' => 'nullable|array',
            'previous_jobs.*' => 'array:company,position,start_date,end_date',
            'cotisant_signature' => 'required|string',
        ], [
            // Custom validation messages
            'required' => 'Le champ :attribute est obligatoire.',
            'image' => 'Le champ :attribute doit être une image.',
            'mimes' => 'Le fichier :attribute doit être de type :values.',
            'max.file' => 'Le fichier :attribute ne doit pas dépasser :max kilo-octets.',
            'string' => 'Le champ :attribute doit être une chaîne de caractères.',
            'date' => 'Le champ :attribute doit être une date valide.',
            'unique' => 'Le :attribute est déjà utilisé.',
            'exists' => 'Le :attribute sélectionné est invalide.',
            'numeric' => 'Le champ :attribute doit être un nombre.',
            'min.numeric' => 'Le champ :attribute doit être au moins :min.',
            'array' => 'Le champ :attribute doit être un tableau.',
            'array_keys' => 'Le champ :attribute doit contenir les clés : :values.',
            'nif.unique' => 'Ce NIF est déjà utilisé.',
            'ninu.unique' => 'Ce NINU est déjà utilisé.',
            'gender_id.exists' => 'Le genre sélectionné est invalide.',
            'civil_status_id.exists' => 'Le statut civil sélectionné est invalide.',
        ], [
            // Custom attribute names
            'profile_photo' => 'photo de profil',
            'institution' => 'institution',
            'lastname' => 'nom',
            'firstname' => 'prénom',
            'mother_lastname' => 'nom de la mère',
            'mother_firstname' => 'prénom de la mère',
            'birth_place' => 'lieu de naissance',
            'birth_date' => 'date de naissance',
            'nif' => 'NIF',
            'ninu' => 'NINU',
            'gender_id' => 'genre',
            'civil_status_id' => 'statut civil',
            'spouse_lastname' => 'nom du conjoint',
            'spouse_firstname' => 'prénom du conjoint',
            'dependents' => 'personnes à charge',
            'entry_date' => 'date d\'entrée',
            'current_salary' => 'salaire actuel',
            'previous_jobs' => 'emplois précédents',
            'cotisant_signature' => 'signature du cotisant',
            'dependents.*.name' => 'nom de la personne à charge',
            'dependents.*.relationship' => 'relation',
            'dependents.*.age' => 'âge',
            'previous_jobs.*.company' => 'entreprise',
            'previous_jobs.*.position' => 'poste',
            'previous_jobs.*.start_date' => 'date de début',
            'previous_jobs.*.end_date' => 'date de fin',
        ]);
    
        // Create new adhesion request
        $request = JoiningRequest::create([
            'institution' => $validatedData['institution'],
            'lastname' => $validatedData['lastname'],
            'firstname' => $validatedData['firstname'],
            'mother_lastname' => $validatedData['mother_lastname'],
            'mother_firstname' => $validatedData['mother_firstname'],
            'birth_place' => $validatedData['birth_place'],
            'birth_date' => $validatedData['birth_date'],
            'nif' => $validatedData['nif'],
            'ninu' => $validatedData['ninu'],
            'gender_id' => $validatedData['gender_id'],
            'civil_status_id' => $validatedData['civil_status_id'],
            'spouse_lastname' => $validatedData['spouse_lastname'],
            'spouse_firstname' => $validatedData['spouse_firstname'],
            'dependents' => json_encode($validatedData['dependents'] ?? []),
            'entry_date' => $validatedData['entry_date'],
            'current_salary' => $validatedData['current_salary'],
            'previous_jobs' => json_encode($validatedData['previous_jobs'] ?? []),
            'cotisant_signature' => $validatedData['cotisant_signature'],
        ]);
    
        // Handle file upload
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('profile_photos', 'public');
            $request->update(['profile_photo' => $path]);
        }
    
        return redirect()->back()->with('success', 'Demande d\'adhésion soumise avec succès !');
    }
}
