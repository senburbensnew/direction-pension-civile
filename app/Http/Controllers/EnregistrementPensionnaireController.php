<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EnregistrementPensionnaireController extends Controller
{
    public function create()
    {
        return view('enregistrement-pensionnaire.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Personal Information
            'code_pensionnaire' => 'required|string|unique:formalites',
            'cin_pensionnaire' => 'nullable|string',
            'nif_pensionnaire' => 'required|string|unique:formalites',
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'no_moniteur' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',

            // Pension Information
            'date_liquidation_pension' => 'nullable|date',
            'date_pension' => 'required|date',
            'statut_pension' => 'required|in:EN PENSION,RETRAITE,SUSPENDU,DECEDE',
            'mode_paiement' => 'required|in:CHEQUES,VIREMENT,ESPECES,COMPENSATION',
            'aval' => 'required|string',
            'montant_pension' => 'required|numeric|min:0',
            'personne_contact' => 'required|string|max:255',
            'mandataire' => 'nullable|string|max:255',

            // Classification & Location
            'localite' => 'required|in:GRAND COMMIS,PE,PP PORT-AU-PRINCE,PP DELMAS,PP PETION-VILLE,PP CARREFOUR,PP PLAINE,PP LEOGANE,WI HINCHE,XA AQUIN,XC CAYES,XF FORT-LIBERTE,XG GONAIVES,XH CAP-HAITIEN,XJ JEREMIE,XL JACMEL,XM MIRAGOANE,XP PETIT-GOAVE,XS SAINT-MARC,XX PORT-DE-PAIX,PX PENSION SPECIALE,CANADA,USA,FRANCE,AUTRES',
            'statut_matrimonial' => 'required|in:Marié(e),Célibataire,Veuf/Veuve,Concubinage,Séparé(e)',
            'cat_pension' => 'nullable|in:Pension Civile,Pension Militaire,Pension BNDAI,Pension Minoterie,Selection Nationale',
            'type_pension' => 'required|in:25 ANS DE SERVICE,30 ANS DE SERVICE,10 ANS DE SERVICE/INFIRME,REVERSIBILITE / VEUF(VE),REVERSIBILITE / MINEUR,REVERSIBILITE / MAJEUR,REVERSIBILITE / PERE,REVERSIBILITE / MERE,40 ANS DE SERVICE,PENSION SPECIALE RETRAITE ANTICIPEE,DECEDE(E),EN FONCTION,HANDICAPE(E),PERTE DE DROIT,PENSION SPECIALE,MONTANT TRANSFERE,20 ANS DE SERVICE',
            'plan_assurance' => 'nullable|in:Plan 1,Plan 2,Plan 3',

            // Contact Information
            'adresse' => 'required|string|max:500',
            'telephone' => 'required|string|regex:/^[0-9]{8}$/',
            'email' => 'nullable|email',

            // Declaration
            'declaration' => 'required|accepted',

            // Dependants
            'dependant_nif' => 'nullable|array',
            'dependant_nif.*' => 'nullable|string',
            'dependant_nom' => 'nullable|array',
            'dependant_nom.*' => 'nullable|string|max:255',
            'dependant_prenom' => 'nullable|array',
            'dependant_prenom.*' => 'nullable|string|max:255',
            'dependant_lien' => 'nullable|array',
            'dependant_lien.*' => 'nullable|in:Conjoint,Enfant,Pere,Mere,Beau-pere,Belle-mere,Grand-pere,Grand-mere,Frere,Soeur,Neveu,Niece,Oncle,Tante,Cousin,Cousine,Autre',
            'assurance' => 'nullable|array',
            'assurance.*' => 'nullable|in:Oui,Non',
            'dependant_naissance' => 'nullable|array',
            'dependant_naissance.*' => 'nullable|date',
            'dependant_notes' => 'nullable|array',
            'dependant_notes.*' => 'nullable|string|max:500',
        ]);

        try {
            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
                $validated['photo'] = $photoPath;
            }

            // Process dependants data
            $dependants = [];
            if ($request->has('dependant_nif')) {
                $dependantCount = count($request->dependant_nif);

                for ($i = 0; $i < $dependantCount; $i++) {
                    // Only add dependant if at least one field is filled
                    if (!empty($request->dependant_nif[$i]) ||
                        !empty($request->dependant_nom[$i]) ||
                        !empty($request->dependant_prenom[$i])) {

                        $dependants[] = [
                            'nif' => $request->dependant_nif[$i] ?? null,
                            'nom' => $request->dependant_nom[$i] ?? null,
                            'prenom' => $request->dependant_prenom[$i] ?? null,
                            'lien_parente' => $request->dependant_lien[$i] ?? null,
                            'assurance' => $request->assurance[$i] ?? null,
                            'date_naissance' => $request->dependant_naissance[$i] ?? null,
                            'notes' => $request->dependant_notes[$i] ?? null,
                        ];
                    }
                }
            }

            $validated['dependants'] = $dependants;

            // Create formalite
            $formalite = Formalite::create($validated);

            return redirect()->route('formalites.index')
                ->with('success', 'Pensionnaire enregistré avec succès!');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage())
                ->withInput();
        }
    }
}
