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
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use App\Enums\TypeDemandeEnum;
use App\Models\DemandeHistory;
use App\Models\PensionCategory;
use App\Helpers\RegexExpressions;
use Illuminate\Support\Facades\DB;
use App\Helpers\CodeGeneratorService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\StoreEtatCarriereRequest;
use App\Http\Requests\StoreArretPaiementRequest;
use App\Http\Requests\StoreDemandePensionRequest;
use App\Http\Requests\StoreDemandeAdhesionRequest;
use App\Http\Requests\StorePreuveExistenceRequest;
use App\Http\Requests\StoreTransfertChequeRequest;
use App\Http\Requests\StoreDemandeAttestationRequest;
use App\Http\Requests\StoreDemandeReinsertionRequest;
use App\Http\Requests\StoreDemandeArretVirementRequest;
use App\Http\Requests\StoreDemandePensionReversionRequest;
use App\Http\Requests\StoreDemandeVirementBancaireRequest;

class DemandeController extends Controller
{
    // DEMANDES DE VIREMENTS BANCAIRES
    public function createDemandeVirement()
    {
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $pensionTypes = PensionType::orderBy('name', 'asc')->get();
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();

        return view('pensionnaire.demande-virement-bancaire', compact('genders', 'civilStatuses', 'pensionTypes', 'pensionCategories'));
    }

    public function storeDemandeVirement(StoreDemandeVirementBancaireRequest $request)
    {
        DB::beginTransaction();
        $storedFilePaths = [];

        try {
            $validated = $request->validated();

            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value,
                (new Demande())->getTable()
            );

            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value;

            $basePath = 'demandes/virement-bancaire/' . now()->format('Y/m');

            // Upload photo
            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store($basePath, 'public');
                $validated['profile_photo'] = $path;
                $storedFilePaths[] = $path;
            }

            // Data métier
            $validated['data'] = collect($validated)
                ->except(['consentement'])
                ->toArray();

            $demande = Demande::create($validated);

            DemandeHistory::create([
                'demande_id' => $demande->id,
                'statut'     => $demande->status->name,
                'commentaire'=> 'Demande créée',
                'changed_by' => auth()->id(),
                'data'       => $demande->data,
            ]);

            DB::commit();

            return back()->with('success', 'Demande enregistrée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

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

    public function storeDemandeAttestation(StoreDemandeAttestationRequest $request)
    {

        DB::beginTransaction();

        try {
            $validated = $request->validated();

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

           return back()->with('success', 'Demande enregistrée avec succès.');
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

    public function storeDemandeTransfertCheque(StoreTransfertChequeRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();

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

            return back()->with('success', 'Demande enregistrée avec succès.');
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

    public function storeDemandeArretPaiement(StoreArretPaiementRequest $request)
    {
        DB::beginTransaction();

        $storedFilePaths = [];

        try {
            // ✅ Validation
            $validated = $request->validated();

            // ❌ Champs non persistés
            unset(
                $validated['pieces'],
                $validated['consentement']
            );

            // ✅ Métadonnées
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value,
                (new Demande())->getTable()
            );

            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value;

            // 📂 Dossier upload
            $basePath = 'demandes/arret-paiement/' . now()->format('Y/m');
            $uploadedFiles = [];

            if ($request->hasFile('pieces')) {
                foreach ($request->file('pieces') as $file) {
                    $path = $file->store($basePath, 'public');
                    $storedFilePaths[] = $path;
                    $uploadedFiles[] = $path;
                }
            }

            // 🧾 Données métier UNIQUEMENT
            $dataMetier = Arr::except($validated, [
                'code',
                'status_id',
                'created_by',
                'type'
            ]);

            $validated['data'] = $dataMetier;
            $validated['data']['pieces'] = $uploadedFiles;


            // 📝 Création
            $demande = Demande::create($validated);

            // 📜 Historique
            DemandeHistory::create([
                'demande_id' => $demande->id,
                'statut' => $demande->status->name,
                'commentaire' => 'Demande créée',
                'changed_by' => auth()->id(),
                'data' => $demande->data
            ]);

            DB::commit();

            return back()->with(
                'success',
                'Demande d’arrêt de paiement enregistrée avec succès.'
            );

        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

            Log::error('Erreur demande arrêt paiement', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

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

    public function storeDemandeReinsertion(StoreDemandeReinsertionRequest $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validated();
 
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

           return back()->with('success', 'Demande enregistrée avec succès.');
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

    public function storeDemandeArretVirement(StoreDemandeArretVirementRequest $request)
    {
        DB::beginTransaction();

        try {
            // ✅ Données déjà validées par le FormRequest
            $validated = $request->validated();

            // Génération du code
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value,
                (new Demande())->getTable()
            );

            $validated['status_id']  = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type']       = TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value;

            /**
             * 📦 Payload métier stocké dans la colonne data (JSON)
             * On exclut les champs techniques
             */
            $validated['data'] = collect($validated)
                ->except(['status_id', 'created_by', 'type'])
                ->toArray();

            // Création de la demande
            $demande = Demande::create($validated);

            // Historique
            DemandeHistory::create([
                'demande_id' => $demande->id,
                'statut'     => $demande->status->name,
                'commentaire'=> 'Demande créée',
                'changed_by' => auth()->id(),
                'data'       => $demande->data,
            ]);

            DB::commit();

            return back()->with('success', 'Demande enregistrée avec succès.');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erreur Demande Arrêt Virement', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

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

    public function storePreuveExistence(StorePreuveExistenceRequest $request)
    {
        DB::beginTransaction();
        $storedFilePaths = [];

        try {
            $validated = $request->validated();

            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value,
                (new Demande())->getTable()
            );

            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value;

            // Files
            $uploadedFiles = [];
            $basePath = 'demandes/preuve-existence/' . now()->format('Y/m');

            if ($request->hasFile('profile_photo')) {
                $path = $request->file('profile_photo')->store($basePath, 'public');
                $uploadedFiles['profile_photo'] = $path;
                $storedFilePaths[] = $path;
            }

            // Data payload
            $validated['data'] = array_merge(
                collect($validated)->except(['profile_photo'])->toArray(),
                ['documents' => $uploadedFiles]
            );

            unset($validated['profile_photo']);

            $demande = Demande::create($validated);

            DemandeHistory::create([
                'demande_id' => $demande->id,
                'statut' => $demande->status->name,
                'commentaire' => 'Demande créée',
                'changed_by' => auth()->id(),
                'data' => $demande->data
            ]);

            DB::commit();

            return back()->with('success', 'Demande enregistrée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();

            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

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

    public function storeDemandeEtatCarriere(StoreEtatCarriereRequest $request)
    {

        DB::beginTransaction();

        // 👉 Tableau pour tracer tous les fichiers stockés
        $storedFilePaths = [];

        try {
            $validated = $request->validated();
 
            // ----------------------------------
            // Generate request metadata
            // ----------------------------------
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value;

            // ----------------------------------
            // Handle file uploads
            // ----------------------------------
            $uploadedFiles = [];
            $basePath = 'demandes/etat_carriere/'. now()->format('Y/m');

            // Helper pour fichiers multiples
            $storeMultiple = function ($files) use ($basePath, &$storedFilePaths) {
                if (!$files) {
                    return [];
                }

                return collect($files)->map(function ($file) use ($basePath, &$storedFilePaths) {
                    $path = $file->store($basePath, 'public');
                    $storedFilePaths[] = $path;
                    return $path;
                })->toArray();
            };

            // ================================
            // 1️⃣ FICHIERS MULTIPLES
            // ================================
            $multipleFields = [
                'bulletins_salaire',
                'documents_carriere'
            ];

            foreach ($multipleFields as $field) {
                $uploadedFiles[$field] = $storeMultiple($request->file($field));
            }


            // ================================
            // 2️⃣ FICHIERS SIMPLES
            // ================================
            $singleFields = [
                'copie_piece_identite',
                'lettre_nomination',
                'acte_mariage_acte_deces'
            ];

            foreach ($singleFields as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store($basePath, 'public');
                    $uploadedFiles[$field] = $path;
                    $storedFilePaths[] = $path;
                }
            }

            // ----------------------------------
            // Build data payload
            // ----------------------------------
            $validated['data'] = array_merge(
                collect($validated)->except([
                    'bulletins_salaire',
                    'documents_carriere',
                    'copie_piece_identite',
                    'lettre_nomination',
                    'acte_mariage_acte_deces'
                ])->toArray(),
                ['documents' => $uploadedFiles]
            );

            // Nettoyage avant création
            unset(
                $validated['bulletins_salaire'],
                $validated['documents_carriere'],
                $validated['copie_piece_identite'],
                $validated['lettre_nomination'],
                $validated['acte_mariage_acte_deces'],
            );

           $demande = Demande::create($validated);

           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

                        // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

                        // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // demandePension
    public function demandePension(){
        return view('fonctionnaire.demande-pension');
    }

    // DEMANDE DE PENSION Standard
    public function createDemandePensionStandard(){
        return view('fonctionnaire.demande-pension-standard');
    }

    public function storeDemandePensionStandard(StoreDemandePensionRequest $request)
    {
        DB::beginTransaction();

        // 👉 Tableau pour tracer tous les fichiers stockés
        $storedFilePaths = [];

        try {
            $validated = $request->validated();

            // ----------------------------------
            // Generate request metadata
            // ----------------------------------
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_PENSION->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_PENSION->value;

            // ----------------------------------
            // Handle file uploads
            // ----------------------------------
            $uploadedFiles = [];
            $basePath = 'demandes/pension/'. now()->format('Y/m');

            // Helper pour fichiers multiples
            $storeMultiple = function ($files) use ($basePath, &$storedFilePaths) {
                if (!$files) {
                    return [];
                }

                return collect($files)->map(function ($file) use ($basePath, &$storedFilePaths) {
                    $path = $file->store($basePath, 'public');
                    $storedFilePaths[] = $path;
                    return $path;
                })->toArray();
            };

            // ================================
            // 1️⃣ FICHIERS MULTIPLES
            // ================================
            $multipleFields = [
                'career_certificates',
                'marriage_certificates',
                'birth_certificates',
                'tax_id_numbers',
                'photos',
            ];

            foreach ($multipleFields as $field) {
                $uploadedFiles[$field] = $storeMultiple($request->file($field));
            }

            // ================================
            // 2️⃣ FICHIERS SIMPLES
            // ================================
            $singleFields = [
                'monitor_copy',
                'medical_certificate',
                'check_stub',
                'divorce_certificate',
            ];

            foreach ($singleFields as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store($basePath, 'public');
                    $uploadedFiles[$field] = $path;
                    $storedFilePaths[] = $path;
                }
            }

            // ----------------------------------
            // Build data payload
            // ----------------------------------
            $validated['data'] = array_merge(
                collect($validated)->except([
                    'career_certificates',
                    'marriage_certificates',
                    'birth_certificates',
                    'tax_id_numbers',
                    'photos',
                    'monitor_copy',
                    'medical_certificate',
                    'check_stub',
                    'divorce_certificate',
                ])->toArray(),
                ['documents' => $uploadedFiles]
            );

            // Nettoyage avant création
            unset(
                $validated['career_certificates'],
                $validated['marriage_certificates'],
                $validated['birth_certificates'],
                $validated['tax_id_numbers'],
                $validated['photos'],
                $validated['monitor_copy'],
                $validated['medical_certificate'],
                $validated['check_stub'],
                $validated['divorce_certificate']
            );

            // ----------------------------------
            // Create Demande
            // ----------------------------------
            $demande = Demande::create($validated);

            // ----------------------------------
            // History
            // ----------------------------------
            DemandeHistory::create([
                'demande_id' => $demande->id,
                'statut' => $demande->status->name,
                'commentaire' => 'Demande créée',
                'changed_by' => auth()->id(),
                'data' => $demande->data,
            ]);

            DB::commit();

            return back()->with('success', 'Demande enregistrée avec succès.');

        }catch (\Exception $e) {

            DB::rollBack();

            // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDE DE PENSION DE REVERSION
    public function createDemandePensionReversion(){
        return view('fonctionnaire.demande-pension-reversion');
    }

    public function storeDemandePensionReversion(StoreDemandePensionReversionRequest $request)
    {

        DB::beginTransaction();

        // 👉 Tableau pour tracer tous les fichiers stockés
        $storedFilePaths = [];

        try {
            $validated = $request->validated();
 
            // ----------------------------------
            // Generate request metadata
            // ----------------------------------
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                (new Demande())->getTable()
            );
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = auth()->id();
            $validated['type'] = TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value;

            // ----------------------------------
            // Handle file uploads
            // ----------------------------------
            $uploadedFiles = [];
            $basePath = 'demandes/pension-reversion/'. now()->format('Y/m');

            // Helper pour fichiers multiples
            $storeMultiple = function ($files) use ($basePath, &$storedFilePaths) {
                if (!$files) {
                    return [];
                }

                return collect($files)->map(function ($file) use ($basePath, &$storedFilePaths) {
                    $path = $file->store($basePath, 'public');
                    $storedFilePaths[] = $path;
                    return $path;
                })->toArray();
            };

            // ================================
            // 1️⃣ FICHIERS MULTIPLES
            // ================================
            $multipleFields = [
                'acte_deces',
                'photos_identite',
                'attestation_scolaires',
            ];

            foreach ($multipleFields as $field) {
                $uploadedFiles[$field] = $storeMultiple($request->file($field));
            }


            // ================================
            // 2️⃣ FICHIERS SIMPLES
            // ================================
            $singleFields = [
                'certificat_carriere',
                'certificat_non_dissolution',
                'carte_pension',
                'souche_cheque',
                'extrait_acte_mariage',
                'extrait_acte_naissance',
                'matricule_fiscal',
                'carte_electorale',
                'pv_tutelle',
                'certificat_medical',
                'copie_moniteur'
            ];

            foreach ($singleFields as $field) {
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store($basePath, 'public');
                    $uploadedFiles[$field] = $path;
                    $storedFilePaths[] = $path;
                }
            }

            // ----------------------------------
            // Build data payload
            // ----------------------------------
            $validated['data'] = array_merge(
                collect($validated)->except([
                    'acte_deces',
                    'photos_identite',
                    'attestation_scolaires',
                    'certificat_carriere',
                    'certificat_non_dissolution',
                    'carte_pension',
                    'souche_cheque',
                    'extrait_acte_mariage',
                    'extrait_acte_naissance',
                    'matricule_fiscal',
                    'carte_electorale',
                    'pv_tutelle',
                    'certificat_medical',
                    'copie_moniteur'
                ])->toArray(),
                ['documents' => $uploadedFiles]
            );

            // Nettoyage avant création
            unset(
                $validated['acte_deces'],
                $validated['photos_identite'],
                $validated['attestation_scolaires'],
                $validated['certificat_carriere'],
                $validated['certificat_non_dissolution'],
                $validated['carte_pension'],
                $validated['souche_cheque'],
                $validated['extrait_acte_mariage'],
                $validated['extrait_acte_naissance'],
                $validated['matricule_fiscal'],
                $validated['carte_electorale'],
                $validated['pv_tutelle'],
                $validated['certificat_medical'],
                $validated['copie_moniteur']
            );

            // ----------------------------------
            // Create Demande
            // ----------------------------------
           $demande = Demande::create($validated);

            // ----------------------------------
            // History
            // ----------------------------------
           DemandeHistory::create([
               'demande_id' => $demande->id,
               'statut' => $demande->status->name,
               'commentaire' => 'Demande créée',
               'changed_by' => auth()->id(),
               'data' => $demande->data
           ]);

           DB::commit();

           return back()->with('success', 'Demande enregistrée avec succès.');
        } catch (ValidationException $e) {
            DB::rollBack();

            // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

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

    public function storeDemandeAdhesion(StoreDemandeAdhesionRequest $request)
    {
        DB::beginTransaction();

        // 👉 Tableau pour tracer tous les fichiers stockés
        $storedFilePaths = [];

        try {
            $validated = $request->validated();

            $basePath = 'demandes/adhesion/' . now()->format('Y/m');

            // =========================
            // Upload photo de profil
            // =========================
            if ($request->hasFile('profile_photo')) {
                $path = $request
                    ->file('profile_photo')
                    ->store($basePath, 'public');

                $validated['profile_picture'] = $path;
                $storedFilePaths[] = $path;
            }

            // =========================
            // Séparer data métier
            // =========================
            $data = collect($validated)->except([
                'profile_photo',
                'consentement',
            ])->toArray();

            // =========================
            // Création demande
            // =========================
            $demande = Demande::create([
                'code'       => CodeGeneratorService::generateUniqueRequestCode(
                    TypeDemandeEnum::DEMANDE_ADHESION->value,
                    (new Demande())->getTable()
                ),
                'status_id'  => Status::getStatusPending()->id,
                'created_by' => auth()->id(),
                'type'       => TypeDemandeEnum::DEMANDE_ADHESION->value,
                'data'       => $data,
            ]);

            // =========================
            // Historique
            // =========================
            DemandeHistory::create([
                'demande_id' => $demande->id,
                'statut'     => $demande->status->name,
                'commentaire'=> 'Demande créée',
                'changed_by' => auth()->id(),
                'data'       => $demande->data,
            ]);

            DB::commit();

            return back()->with('success', 'Demande enregistrée avec succès.');
        } catch (\Throwable $e) {

            DB::rollBack();

            // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

            Log::error('Erreur demande adhésion', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }
}
