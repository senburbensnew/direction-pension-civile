<?php

namespace App\Http\Controllers;

use App\Enums\RequestEventTypeEnum;
use App\Enums\RequestTypeEnum;
use App\Models\CheckTransferRequests;
use App\Helpers\RegexExpressions;
use App\Models\ExistenceProofRequest;
use App\Models\PaymentStopRequests;
use App\Models\Gender;
use App\Models\PensionCategory;
use App\Models\PensionType;
use App\Models\RequestHistory;
use App\Models\Status;
use App\Models\CivilStatus;
use App\Rules\Base64Image;
use Illuminate\Http\Request;
use App\Models\BankTransferRequests;
use App\Helpers\CodeGeneratorService;
use Illuminate\Support\Facades\DB;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\Storage;

class PensionnaireController extends Controller
{

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!$request->user() || !$request->user()->can('viewPensionnaireMenu')) {
                abort(403, 'Unauthorized action.');
            }
            return $next($request);
        });
    }    

    public function demandeVirement()
    {
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $pensionTypes = PensionType::orderBy('name', 'asc')->get();
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();
        return view('pensionnaire.demande-virement', compact('genders', 'civilStatuses', 'pensionTypes', 'pensionCategories'));
    }

    public function processVirementRequest(Request $request)
    {
        // Custom validation attributes
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
        ];
    
        // Custom validation messages
        $messages = [
            'required' => 'Le champ :attribute est obligatoire.',
            'unique' => 'Ce code est déjà utilisé.',
            'digits' => 'Le :attribute doit contenir exactement :digits chiffres.',
            'digits_between' => 'Le :attribute doit contenir entre :min et :max chiffres.',
            'numeric' => 'Le :attribute doit être un nombre valide.',
            'date' => 'La date de naissance est invalide.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'max.string' => 'Le :attribute ne doit pas dépasser :max caractères.',
            'max.file' => 'La photo ne doit pas dépasser :max Ko.',
            'image' => 'Le fichier doit être une image valide (JPG, PNG, JPEG).',
            
            // Field-specific messages
            'birth_date.date' => 'Veuillez entrer une date de naissance valide.',
            'pensioner_code.required' => 'Le code pensionnaire est obligatoire.',
            'in' => 'La valeur sélectionnée pour :attribute est invalide.',
            'base64_image' => 'Le :attribute doit être une image valide en base64.',
            'regex' => 'Le format du champ :attribute est invalide.',
            'fiscal_year.regex' => 'Le format de l\'année fiscale est invalide. Il doit être sous la forme 20XX/20XX (ex: 2023/2024).',
            'signature.max' => 'Le :attribute ne doit pas dépasser 2 Mo.',
            'profile_photo.required' => 'Veuillez sélectionner une photo',
            'profile_photo.image' => 'Le fichier doit être une image',
            'profile_photo.mimes' => 'Format acceptés : jpeg, png, jpg, gif, webp',
            'profile_photo.max' => 'La taille ne doit pas dépasser 2 Mo',
        ];
    
        try {
            $validated = $request->validate([
                'pensioner_code' => 'required',
                'pension_type_id' => 'required|exists:pension_types,id',
                'nif' => [
                    'required',
                    'string',
                    'regex:' . RegexExpressions::nif()
                ],
                'full_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'birth_date' => 'required|date',
                'civil_status_id' => 'required|exists:civil_statuses,id',
                'gender_id' => 'required|exists:genders,id',
                'allocation_amount' => 'required|numeric',
                'mother_name' => 'required|string|max:255',
                'phone' => [
                    'required',
                    'string',
                    // 'regex:' . RegexExpressions::phoneNumber()
                ],
                'pension_category_id' => 'required|exists:pension_categories,id',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|digits_between:5,20',
                'account_name' => 'required|string|max:255',
                'status_id' => 'sometimes|exists:statuses,id',
                'signature' => [
                    'required',
                    'string',
                    new Base64Image,
                ],
                'profile_photo' => [
                    'required',
                    'image',
                    'mimes:jpeg,png,jpg,gif,webp',
                    'max:2048'
                ],
            ], $messages, $attributes);
    
            // Generate unique request code
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode('VIR_BANC', (new BankTransferRequests())->getTable());
            $validated['status_id'] = Status::getStatusPending()->id;
    
            // Process signature
            $signaturePath = Helpers::processBase64Image(
                $request->input('signature'),
                'signatures',
                'Signature upload failed',
                1024 * 1024 * 2, // 2MB
                'public'
            );
                
            // Process profile photo
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
    
            $validated['pensioner_signature'] = $signaturePath;
            $validated['profile_photo'] = $profilePhotoPath;
            unset($validated['signature']);
    
            $validated['created_by'] = auth()->id();
    
            $bankTransferRequest = DB::transaction(function () use ($validated) {
                // Create the BankTransferRequest
                $bankTransferRequest = BankTransferRequests::create($validated);
    
                // Create the associated RequestHistory
                RequestHistory::store(
                    $bankTransferRequest->id,
                    RequestTypeEnum::BANK_TRANSFER_REQUEST,
                    json_encode($bankTransferRequest),
                    RequestEventTypeEnum::REQUEST_CREATED,
                    $bankTransferRequest->created_at,
                    auth()->id()
                );
    
                return $bankTransferRequest;
            });       
    
            return redirect()->back()
                ->with('success', 'Demande de virement enregistrée avec succès! Un email de confirmation vous a été envoyé.');
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Submission Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    
            $errorMessage = "Une erreur inattendue est survenue. Veuillez réessayer.";

            if (isset($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }
            if (isset($profilePhotoPath)) {
                Storage::disk('public')->delete($profilePhotoPath);
            }
    
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    public function demandeAttestation()
    {
        return view('pensionnaire.demande-attestation');
    }

    public function processAttestationRequest(Request $request)
    {
        // Validation logic
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            // Add more fields as needed
        ]);

        // Handle the attestation request logic here

        return redirect()->route('pensionnaire.attestation-request-form')->with('success', 'Attestation request submitted successfully.');
    }

    public function demandeTransfertCheque()
    {
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();
        return view('pensionnaire.demande-transfert-cheque', compact('pensionCategories'));
    }

    public function processCheckTransferRequest(Request $request)
    {

        try{
            // Custom validation attributes
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
        
            // Custom validation messages
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

            // Validation logic
            $validatedData = $request->validate([
                'signature' => [
                    'required',
                    'string',
                    new Base64Image,
                ],
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

            // Process signature
            $signaturePath = Helpers::processBase64Image(
                            $request->input('signature'),
                            'signatures',
                            'Signature upload failed',
                            1024 * 1024 * 2, // 2MB
                            'public'
            );
                             
            $validatedData['pensioner_signature'] = $signaturePath;
            unset($validatedData['signature']);

            $validatedData['code'] = CodeGeneratorService::generateUniqueRequestCode('TRANSF_CHEQ', (new CheckTransferRequests())->getTable());
            $validatedData['status_id'] = Status::getStatusPending()->id;
            $validatedData['created_by'] = auth()->id();

            $checkTransferRequest = DB::transaction(function () use ($validatedData) {
                // Create the BankTransferRequest
                $checkTransferRequest = CheckTransferRequests::create($validatedData);
    
                // Create the associated RequestHistory
                RequestHistory::store(
                    $checkTransferRequest->id,
                    RequestTypeEnum::CHECK_TRANSFER_REQUEST,
                    json_encode($checkTransferRequest),
                    RequestEventTypeEnum::REQUEST_CREATED,
                    $checkTransferRequest->created_at,
                    auth()->id()
                );
    
                return $checkTransferRequest;
            });

            return redirect()->route('pensionnaire.check-transfer-request-form')->with('success', 'La demande de transfert a été soumise avec succès.');
       } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Submission Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    
            $errorMessage = "Une erreur inattendue est survenue. Veuillez réessayer.";

            if (isset($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }
            if (isset($profilePhotoPath)) {
                Storage::disk('public')->delete($profilePhotoPath);
            }
    
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    public function demandeArretPaiement()
    {
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();
        return view('pensionnaire.demande-arret-paiement', compact("pensionCategories"));
    }

    public function processPaymentStopRequest(Request $request)
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

        // Custom validation messages
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


        try {
            $validPensionCategories = PensionCategory::pluck('id')->toArray();
            // Validation logic
            $validatedData = $request->validate([
                // 'fiscal_year' => 'required|string|max:255',
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
            ], $messages, $attributes);


            // Process signature
            $signaturePath = Helpers::processBase64Image(
                $request->input('signature'),
                'signatures',
                'Signature upload failed',
                1024 * 1024 * 2, // 2MB
                'public'
            );
                             
            $validatedData['pensioner_signature'] = $signaturePath;
            unset($validatedData['signature']);


            // Handle the payment stop logic here
            $validatedData['code'] = CodeGeneratorService::generateUniqueRequestCode('ARRET_PAIE', (new PaymentStopRequests())->getTable());
            $validatedData['status_id'] = Status::getStatusPending()->id;
            $validatedData['created_by'] = auth()->id();

            $paymentStopRequest = DB::transaction(function () use ($validatedData) {
                // Create the BankTransferRequest
                $paymentStopRequest = PaymentStopRequests::create($validatedData);
    
                // Create the associated RequestHistory
                RequestHistory::store(
                    $paymentStopRequest->id,
                    RequestTypeEnum::PAYMENT_STOP_REQUEST,
                    json_encode($paymentStopRequest),
                    RequestEventTypeEnum::REQUEST_CREATED,
                    $paymentStopRequest->created_at,
                    auth()->id()
                );
    
                return $paymentStopRequest;
            });

            return redirect()->route('pensionnaire.payment-stop-request-form')->with('success', 'La demande de cessation de paiement a été soumise avec succès.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Submission Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    
            $errorMessage = "Une erreur inattendue est survenue. Veuillez réessayer.";

            if (isset($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }
            if (isset($profilePhotoPath)) {
                Storage::disk('public')->delete($profilePhotoPath);
            }
    
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }

    public function demandeReinsertion()
    {
        return view('pensionnaire.demande-reinsertion');
    }

    public function processReinstatementRequest(Request $request)
    {
        // Validation logic
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            // Add more fields as needed
        ]);

        // Handle the reinstatement request logic here

        return redirect()->route('pensionnaire.reinstatement-request-form')->with('success', 'Reinstatement request submitted successfully.');
    }

    public function demandeArretVirement()
    {
        return view('pensionnaire.demande-arret-virement');
    }

    public function processTransferStopRequest(Request $request)
    {
        // Validation logic
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            // Add more fields as needed
        ]);

        // Handle the transfer stop logic here

        return redirect()->route('pensionnaire.transfer-stop-request-form')->with('success', 'Transfer stop request submitted successfully.');
    }

    public function preuveExistence()
    {    
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();

        return view('pensionnaire.preuve-existence', compact('genders', 'civilStatuses', 'pensionCategories'));
    }

    public function processExistenceProofRequest(Request $request)
    {
        $attributes = [
            'last_proof_mandate_date' => 'Date de derniere preuve/mandat',
            "id_number" => "numéro identité",
            "profile_photo" => "photo profil",
            "fiscal_year" => "année fiscale",
            "nif" => "NIF",
            "lastname" => "nom",
            "firstname" => "prénom",
            "address" => "adresse",
            "location" => "localisation",
            "birth_date" => "date de naissance",
            "civil_status_id" => "état civil",
            "gender_id" => "sexe",
            "postal_address" => "adresse postale",
            "phone" => "téléphone",
            "pension_amount" => "montant",
            "monitor_number" => "numéro moniteur",
            "monitor_date" => "date moniteur",
            "pension_start_date" => "date début pension",
            "pension_end_date" => "date fin pension",
            "pension_category_id" => "nature pension",
            "signature" => "signature",
        ];
    
        $messages = [
            'required' => 'Le champ :attribute est obligatoire.',
            'exists' => 'La valeur sélectionnée pour :attribute est invalide.',
            'digits' => 'Le :attribute doit contenir exactement :digits chiffres.',
            'digits_between' => 'Le :attribute doit contenir entre :min et :max chiffres.',
            'numeric' => 'Le :attribute doit être un nombre valide.',
            'date' => 'La date de :attribute est invalide.',
            'in' => 'La valeur sélectionnée pour :attribute est invalide.',
            'max.string' => 'Le :attribute ne doit pas dépasser :max caractères.',
            'image' => 'Le fichier :attribute doit être une image valide (JPG, PNG, JPEG).',
            'base64_image' => 'Le :attribute doit être une image valide en base64.',
            'regex' => 'Le format du champ :attribute est invalide.',
            'fiscal_year.regex' => 'Le format de l\'année fiscale est invalide. Il doit être sous la forme 20XX/20XX (ex: 2023/2024).',
            'signature.max' => 'Le :attribute ne doit pas dépasser 2 Mo.',
        ];
    
        try {
            $validationRules = [
                "id_number" => [
                    "required",
                    "string",
                    "regex:" . RegexExpressions::ninu()
                ],
                "nif" => "required|string|max:255",
                "lastname" => "required|string|max:255",
                "firstname" => "required|string|max:255",
                "address" => "required|string|max:255",
                "location" => "required|string|max:255",
                "birth_date" => "required|date",
                "civil_status_id" => "required|string|exists:civil_statuses,id",
                "gender_id" => "required|string|exists:genders,id",
                "postal_address" => "required|string|max:255",
                "phone" => "required|string|max:255",
                "pension_amount" => "required|numeric",
                "monitor_number" => "required|string|max:255",
                "monitor_date" => "required|date",
                'last_proof_mandate_date' => 'nullable|date',
                "pension_start_date" => "required|date",
                "pension_end_date" => "required|date",
                "pension_category_id" => "required|string|exists:pension_categories,id",
                'fiscal_year' => [
                    'required',
                    'string',
                    "regex:" . RegexExpressions::fiscalYear(),
                ],
                // 'signature' => 'required|max:2800000', // ~2MB base64
                'signature' => [
                    'required',
                    'string',
                    new Base64Image,
                ],
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'dependants' => 'nullable|array',
                'dependants.*.name' => 'required|string',
                'dependants.*.relation' => 'required|string',
                'dependants.*.birth_date' => 'required|date',
                'dependants.*.gender_id' => 'required|string|exists:genders,id',
            ];
    
            $validatedData = $request->validate($validationRules, $messages, $attributes);

            // Process signature
            $signaturePath = Helpers::processBase64Image(
                $request->input('signature'),
                'signatures',
                'Signature upload failed',
                1024 * 1024 * 2, // 2MB
                'public'
            );
    
            // Process profile photo
            $profilePhotoPath = $request->file('profile_photo')->store('profile_photos', 'public');
    
            // Generate request code
            $validatedData['code'] = CodeGeneratorService::generateUniqueRequestCode(
                'PREUVE-EXISTENCE', 
                (new ExistenceProofRequest())->getTable()
            );
    
            // Update validated data with paths
            $validatedData['pensioner_signature'] = $signaturePath;
            $validatedData['profile_photo'] = $profilePhotoPath;
            unset($validatedData['signature']);
    
            // System fields
            $validatedData['status_id'] = Status::getStatusPending()->id;
            $validatedData['created_by'] = auth()->id();
    
            // Create record
            DB::transaction(function () use ($validatedData) {
                $existenceProofRequest = ExistenceProofRequest::create($validatedData);
                    
                if (!empty($validatedData['dependants']) && is_array($validatedData['dependants'])) {
                    $existenceProofRequest->dependants()->createMany($validatedData['dependants']);
                }
                
                $requestHistory = RequestHistory::store(
                    $existenceProofRequest->id,
                    RequestTypeEnum::EXISTENCE_PROOF_REQUEST,
                    json_encode($existenceProofRequest),
                    RequestEventTypeEnum::REQUEST_CREATED,
                    $existenceProofRequest->created_at,
                    auth()->id()
                );
            });            

            // event(new RequestCreated($bankTranferRequest));
    
            return redirect()->route('pensionnaire.preuve-existence')
                ->with('success', 'Demande soumise avec succès.');
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Erreurs dans le formulaire.');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Submission Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    
            if (isset($signaturePath)) {
                Storage::disk('public')->delete($signaturePath);
            }
            if (isset($profilePhotoPath)) {
                Storage::disk('public')->delete($profilePhotoPath);
            }
    
            return redirect()->back()
                ->with('error', 'Erreur inattendue. Veuillez réessayer.')
                ->withInput();
        }
    }

    public function demandePension(){
        return view('pensionnaire.demande_pension'); 
    }

    public function showPensionReversionForm()
    {
        return view('pensionnaire.demande-pension-reversion');
    }

    public function processPensionReversion(Request $request)
    {
        // Handle reversion pension form submission
    }
}