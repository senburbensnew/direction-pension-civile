<?php

namespace App\Http\Controllers;

use Log;
use Exception;
use App\Models\Gender;
use App\Models\Status;
use App\Models\Demande;
use App\Rules\Base64Image;
use App\Models\CivilStatus;
use App\Models\PensionType;
use Illuminate\Http\Request;
use App\Enums\TypeDemandeEnum;
use App\Models\DemandeHistory;
use App\Models\PensionCategory;
use App\Helpers\RegexExpressions;
use Illuminate\Support\Facades\DB;
use App\Helpers\CodeGeneratorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DemandeController extends Controller
{
    // DEMANDES DE VIREMENTS BANCAIRES
    public function createDemandeVirement()
    {
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $pensionTypes = PensionType::orderBy('name', 'asc')->get();
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();
        return view('pensionnaire.demande-virement', compact('genders', 'civilStatuses', 'pensionTypes', 'pensionCategories'));
    }

    public function storeDemandeVirement(Request $request)
    {
        $attributes = [
            'profile_photo' => "photo de profil",
            'pensioner_code' => 'code du pensionné',
            'pension_type_id' => 'type de pension',
            'nif' => 'NIF',
            'full_name' => 'nom complet',
            'address' => 'adresse',
            'city' => 'ville',
            'birth_date' => 'date de naissance',
            'civil_status_id' => 'état civil',
            'gender_id' => 'genre',
            'allocation_amount' => 'montant d\'allocation',
            'mother_name' => 'nom de la mère',
            'phone' => 'téléphone',
            'pension_category_id' => 'catégorie de pension',
            'bank_name' => 'nom de la banque',
            'account_number' => 'numéro de compte',
            'account_name' => 'nom du compte',
            'signature' => 'signature',
            'declaration_accepted' => "déclaration et engagement"
        ];

        $messages = [
            'required' => 'Le champ :attribute est obligatoire.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'numeric' => 'Le :attribute doit être un nombre valide.',
            'digits_between' => 'Le :attribute doit contenir entre :min et :max chiffres.',
            'image' => 'Le fichier doit être une image valide.',
            'profile_photo.max' => 'La taille ne doit pas dépasser 2 Mo',
        ];

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'pensioner_code' => 'required',
                'pension_type_id' => 'required|exists:pension_types,id',
                'nif' => ['required', 'regex:' . RegexExpressions::nif()],
                'full_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'birth_date' => 'required|date',
                'civil_status_id' => 'required|exists:civil_statuses,id',
                'gender_id' => 'required|exists:genders,id',
                'allocation_amount' => 'required|numeric',
                'mother_name' => 'required|string|max:255',
                'phone' => ['required'],
                'pension_category_id' => 'required|exists:pension_categories,id',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|numeric|digits_between:5,20',
                'account_name' => 'required|string|max:255',
                'signature' => ['nullable', 'string', new Base64Image],
                'declaration_accepted' => ['required', 'accepted'],
            ], $messages, $attributes);

            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value;
           
            if ($request->hasFile('profile_photo')) {
                $validated['profile_photo'] = $request->file('profile_photo')
                    ->store('profile_photos', 'public');
            }

           $validated['data'] = collect($validated)->except(['signature'])->toArray();

           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de virement enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDES D'ATTESTATION
    public function createDemandeAttestation()
    {
        return view('pensionnaire.demande-attestation');
    }

    public function storeDemandeAttestation(Request $request)
    {
        $attributes = [
            'pensioner_code' => 'code du pensionné',
            'nif' => 'NIF',
            'firstname' => 'Prénom',
            'lastname' => 'Nom',
            // 'declaration_accepted' => "déclaration et engagement"
        ];

        $messages = [
            'required' => 'Le champ :attribute est obligatoire.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'numeric' => 'Le :attribute doit être un nombre valide.',
            'digits_between' => 'Le :attribute doit contenir entre :min et :max chiffres.',
        ];

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'pensioner_code' => 'required',
                'nif' => ['required', 'regex:' . RegexExpressions::nif()],
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                // 'declaration_accepted' => ['required', 'accepted'],
            ], $messages, $attributes);

            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ATTESTATION->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_ATTESTATION->value;

           $validated['data'] = collect($validated)->toArray();

           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de virement enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDES DE TRANSFERT DE CHEQUES
    public function createDemandeTransfertCheque()
    {
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();
        return view('pensionnaire.demande-transfert-cheque', compact('pensionCategories'));
    }

    public function storeDemandeTransfertCheque(Request $request)
    {
        $attributes = [
                'pensioner_code' => 'code du pensionné',
                'pension_type_id' => 'type de pension',
                'nif' => 'NIF',
                'full_name' => 'nom complet',
                'address' => 'adresse',
                'city' => 'ville',
                'civil_status_id' => 'état civil',
                'gender_id' => 'genre',
                'amount' => 'montant d\'allocation',
                'maiden' => 'nom de la mère',
                'phone' => 'téléphone',
                'fiscal_year' => 'année fiscale',
                'start_month' => 'mois de début',
                'request_date' => 'date de la demande',
                'pension_category_id' => 'régime de pension',
                'lastname' => 'nom',
                'firstname' => 'prénom',
                'maiden_name' => 'nom de jeune fille',
                'ninu' => 'NINU',
                'email' => 'courriel',
                'from' => 'début période',
                'to' => 'fin période',
                'transfer_reason' => 'motif',
        ];
        
        $messages = [
                'required' => 'Le champ :attribute est obligatoire.',
                'unique' => 'Ce code est déjà utilisé.',
                'digits' => 'Le :attribute doit contenir exactement :digits chiffres.',
                'digits_between' => 'Le :attribute doit contenir entre :min et :max chiffres.',
                'numeric' => 'Le :attribute doit être un nombre valide.',
                'date' => 'La date de naissance est invalide.',
                'in' => 'La valeur sélectionnée pour :attribute est invalide.',
                'max.string' => 'Le :attribute ne doit pas dépasser :max caractères.',
                'max.file' => 'La photo ne doit pas dépasser :max Ko.',
                'image' => 'Le fichier doit être une image valide (JPG, PNG, JPEG).',
                
                // Field-specific messages
                'nif.digits' => 'Le NIF doit contenir exactement 10 chiffres.',
                'phone.digits' => 'Le numéro de téléphone doit contenir exactement 10 chiffres.',
                'account_number.digits_between' => 'Le numéro de compte doit contenir entre 5 et 20 chiffres.',
                'birth_date.date' => 'Veuillez entrer une date de naissance valide.',
                'pensioner_code.required' => 'Le code pensionnaire est obligatoire.',
        ];    

        $validPensionCategories = PensionCategory::pluck('id')->toArray();

        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'fiscal_year' => 'required|string|max:255',
                'start_month' => 'required|string|size:7',  // Ensures the month is in the format YYYY-MM
                'request_date' => 'required|date',
                'pension_category_id' => 'required|in:' . implode(',', $validPensionCategories), // Ensures that the pension_category_id exists in the pension_categories table
                'pensioner_code' => 'required|string|max:255',
                'amount' => 'required|numeric',  // Validates the allocation amount as numeric
                'lastname' => 'required|string|max:255',
                'firstname' => 'required|string|max:255',
                'maiden_name' => 'required|string|max:255',
                'nif' => 'required|string|max:255',
                'ninu' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'phone' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'from' => 'required|date',
                'to' => 'required|date',
                'transfer_reason' => 'required|string|max:1000',  // Assumes the transfer reason might be a longer text
            ], $messages, $attributes);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de virement enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDES D'ARRET DE PAIEMENT
    public function createDemandeArretPaiement()
    {
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();
        return view('pensionnaire.demande-arret-paiement', compact('pensionCategories'));
    }

    public function storeDemandeArretPaiement(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                "pensioner_code" => 'required|string|max:255',
                "name" => 'required|string|max:255',
                "company" => 'required|string|max:255',
                "personne_habilitee" => 'required|string|max:255',
                "nom_conjoint" => 'required|string|max:255'
            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de virement enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDES DE REINSERTION
    public function createDemandeReinsertion()
    {
        return view('pensionnaire.demande-reinsertion');
    }

    public function storeDemandeReinsertion(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                "firstname" => 'required|string|max:255',
                "lastname" => 'required|string|max:255',
                "reason" => 'required|string|max:255'
            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_REINSERTION->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_REINSERTION->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de virement enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDES ARRET VIREMENT
    public function createDemandeArretVirement(){
        return view('pensionnaire.demande-arret-virement');
    }

    public function storeDemandeArretVirement(Request $request)
    {


        DB::beginTransaction();

        try {
            $validated = $request->validate([

            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de virement enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // PREUVE EXISTENCE
    public function createPreuveExistence(){
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();

        return view('pensionnaire.preuve-existence', compact('genders', 'civilStatuses', 'pensionCategories'));
    }

    public function storePreuveExistence(Request $request)
    {

        DB::beginTransaction();

        try {
            $validated = $request->validate([

            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de virement enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDE DE PENSION DE REVERSION
    public function createDemandePensionReversion(){
        
        return view('pensionnaire.demande-pension-reversion');
    }

    public function storeDemandePensionReversion(Request $request)
    {

        DB::beginTransaction();

        try {
            $validated = $request->validate([

            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande de pension de reversion enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDE ETAT CARRIERE
    public function createDemandeEtatCarriere(){
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();   
        return view('fonctionnaire.etat-carriere', compact('civilStatuses'));
    }

    public function storeDemandeEtatCarriere(Request $request)
    {

        DB::beginTransaction();

        try {
            $validated = $request->validate([

            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande d\'etat de carriere de reversion enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDE DE PENSION
    public function createDemandePension(){
        return view('fonctionnaire.demande-pension');
    }

    public function storeDemandePension(Request $request)
    {

        DB::beginTransaction();

        try {
            $validated = $request->validate([

            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_PENSION->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_PENSION->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande d\'etat de carriere de reversion enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDE D'ADHESION
    public function createDemandeAdhesion(){
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        return view('institution.demande-adhesion', compact('genders', 'civilStatuses'));
    }

    public function storeDemandeAdhesion(Request $request)
    {

        DB::beginTransaction();

        try {
            $validated = $request->validate([

            ]);
 
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ADHESION->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_ADHESION->value;

           $validated['data'] = collect($validated)->toArray();


           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande d\'adhésion enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }
}
