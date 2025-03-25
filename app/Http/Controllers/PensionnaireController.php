<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BankTransferRequests;
use App\Models\ErrorLog;
use App\Helpers\CodeGeneratorService;
use App\Helpers\ErrorLoggerService;

class PensionnaireController extends Controller
{
    // Display the request for virement form
    public function demandeVirement()
    {
        dd('PensionnaireController.demandeVirement');
        return view('pensionnaire.demande-virement');
    }

    // Process the virement request form
    public function processVirementRequest(Request $request)
    {
        // Custom validation attributes
        $attributes = [
            'pensioner_code' => 'code du pensionné',
            'pension_type' => 'type de pension',
            'nif' => 'NIF',
            'full_name' => 'nom complet',
            'address' => 'adresse',
            'city' => 'ville',
            'birth_date' => 'date de naissance',
            'civil_status' => 'état civil',
            'gender' => 'genre',
            'allocation_amount' => 'montant d\'allocation',
            'mother_name' => 'nom de la mère',
            'phone' => 'téléphone',
            'pension_category' => 'catégorie de pension',
            'bank_name' => 'nom de la banque',
            'account_number' => 'numéro de compte',
            'account_name' => 'nom du compte',
            'photo' => 'photo',
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
            'photo.required' => 'Veuillez sélectionner une photo.',
            'photo.image' => 'Le fichier photo doit être une image valide.',
            'photo.max' => 'La taille de la photo ne doit pas dépasser 2MB.',
            'pensioner_code.required' => 'Le code pensionnaire est obligatoire.',
        ];
    
        try {
            $validated = $request->validate([
                'pensioner_code' => 'required',
                'pension_type' => 'required|in:carriere,reversibilite',
                'nif' => 'required|digits:10',
                'full_name' => 'required|string|max:255',
                'address' => 'required|string|max:255',
                'city' => 'required|string|max:255',
                'birth_date' => 'required|date',
                'civil_status' => 'required|in:célibataire,marié(e),divorcé(e),veuf(ve)',
                'gender' => 'required|in:M,F,A',
                'allocation_amount' => 'required|numeric',
                'mother_name' => 'required|string|max:255',
                'phone' => 'required|digits:10',
                'pension_category' => 'required|in:militaire,civile,bndai,minoterie,selection_nationale',
                'bank_name' => 'required|string|max:255',
                'account_number' => 'required|digits_between:5,20',
                'account_name' => 'required|string|max:255',
                'status' => 'sometimes|in:'.implode(',', BankTransferRequests::getStatusOptions()),
                // 'photo' => 'required|image|max:2048',
            ], $messages, $attributes);
    
            // Generate unique request code
            $validated['code'] = CodeGeneratorService::generateUniqueRequestCode();
            $validated['status'] = BankTransferRequests::STATUS_PENDING;
    
            // Handle file upload
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
                $validated['photo_path'] = $path;
            }
    
            $validated['created_by'] = auth()->id();
    
            BankTransferRequests::create($validated);
    
            return redirect()->back()
                ->with('success', 'Demande de virement enregistrée avec succès! Un email de confirmation vous a été envoyé.');
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', 'Veuillez corriger les erreurs dans le formulaire.');
                
        } catch (\Exception $e) {
            \Log::error('Erreur soumission formulaire: ' . $e->getMessage() . ' - ' . $e->getTraceAsString());
            ErrorLoggerService::logError($e, $request);

            dd($e->getMessage());

            $errorMessage = "Une erreur inattendue est survenue. Veuillez réessayer.";
    
            // Specific error for file upload issues
            if ($e instanceof \Illuminate\Http\Exceptions\PostTooLargeException) {
                $errorMessage = "La taille du fichier photo est trop grande. Veuillez utiliser une image de moins de 2MB.";
            }
    
            return redirect()->back()
                ->with('error', $errorMessage)
                ->withInput();
        }
    }
    

    // Display the request for attestation form
    public function demandeAttestation()
    {
        return view('pensionnaire.demande-attestation');
    }

    // Process the attestation request form
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

    // Display the request for check transfer form
    public function demandeTransfertCheque()
    {
        return view('pensionnaire.demande-transfert-cheque');
    }

    // Process the check transfer request form
    public function processCheckTransferRequest(Request $request)
    {
        // Validation logic
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'amount' => 'required|numeric',
            // Add more fields as needed
        ]);

        // Handle the check transfer logic here

        return redirect()->route('pensionnaire.check-transfer-request-form')->with('success', 'Check transfer request submitted successfully.');
    }

    // Display the request for payment stop form
    public function demandeArretPaiement()
    {
        return view('pensionnaire.demande-arret-paiement');
    }

    // Process the payment stop request form
    public function processPaymentStopRequest(Request $request)
    {
        // Validation logic
        $validatedData = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            // Add more fields as needed
        ]);

        // Handle the payment stop logic here

        return redirect()->route('pensionnaire.payment-stop-request-form')->with('success', 'Payment stop request submitted successfully.');
    }

    // Display the request for reinstatement form
    public function demandeReinsertion()
    {
        return view('pensionnaire.demande-reinsertion');
    }

    // Process the reinstatement request form
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

    // Display the request for transfer stop form
    public function demandeArretVirement()
    {
        return view('pensionnaire.demande-arret-virement');
    }

    // Process the transfer stop request form
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
}
