<?php

namespace App\Http\Controllers;

use App\Enums\RequestEventTypeEnum;
use App\Enums\RequestTypeEnum;
use App\Helpers\CodeGeneratorService;
use App\Helpers\Helpers;
use App\Models\PensionRequest;
use App\Models\RequestHistory;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Status;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        return view('institution.demande_adhesion');
    }

    public function processPensionStandard(Request $request)
    {
        $filePaths = [];
        $photos = [];

        $customMessages = [
                'name.required' => 'Le nom est obligatoire.',
                'nif.required' => 'Le NIF est obligatoire.',

                // Certificat de Carrière
                'career_certificate.required' => 'Le certificat de carrière est obligatoire.',
                'career_certificate.file' => 'Le certificat de carrière doit être un fichier.',
                'career_certificate.mimes' => 'Le certificat de carrière doit être au format PDF.',
                'career_certificate.max' => 'Le certificat de carrière ne doit pas dépasser 2 Mo.',

                // Copie du Moniteur
                'monitor_copy.required' => 'La copie du Moniteur est obligatoire.',
                'monitor_copy.file' => 'La copie du Moniteur doit être un fichier.',
                'monitor_copy.mimes' => 'La copie du Moniteur doit être au format PDF.',
                'monitor_copy.max' => 'La copie du Moniteur ne doit pas dépasser 2 Mo.',

                // Acte de Mariage
                'marriage_certificate.required' => "L'acte de mariage est obligatoire.",
                'marriage_certificate.file' => "L'acte de mariage doit être un fichier.",
                'marriage_certificate.mimes' => "L'acte de mariage doit être au format PDF.",
                'marriage_certificate.max' => "L'acte de mariage ne doit pas dépasser 2 Mo.",

                // Acte de Naissance
                'birth_certificate.required' => "L'acte de naissance est obligatoire.",
                'birth_certificate.file' => "L'acte de naissance doit être un fichier.",
                'birth_certificate.mimes' => "L'acte de naissance doit être au format PDF.",
                'birth_certificate.max' => "L'acte de naissance ne doit pas dépasser 2 Mo.",

                // Acte de Divorce
                'divorce_certificate.file' => "L'acte de divorce doit être un fichier.",
                'divorce_certificate.mimes' => "L'acte de divorce doit être au format PDF.",
                'divorce_certificate.max' => "L'acte de divorce ne doit pas dépasser 2 Mo.",

                // Matricule fiscal + CIN
                'tax_id_number.required' => 'Le matricule fiscal avec CIN est obligatoire.',
                'tax_id_number.file' => 'Le matricule fiscal avec CIN doit être un fichier.',
                'tax_id_number.mimes' => 'Le matricule fiscal avec CIN doit être en PDF, JPG ou PNG.',
                'tax_id_number.max' => 'Le matricule fiscal avec CIN ne doit pas dépasser 2 Mo.',

                // Photos
                'photos.required' => 'Les photos d\'identité sont obligatoires.',
                'photos.array' => 'Les photos doivent être envoyées sous forme de fichiers.',
                'photos.min' => 'Veuillez télécharger exactement 2 photos.',
                'photos.max' => 'Veuillez télécharger exactement 2 photos.',
                'photos.*.image' => 'Les photos doivent être des images.',
                'photos.*.mimes' => 'Les photos doivent être en format JPEG, PNG ou JPG.',
                'photos.*.max' => 'Chaque photo ne doit pas dépasser 1 Mo.',

                // Certificat Médical
                'medical_certificate.required' => 'Le certificat médical est obligatoire.',
                'medical_certificate.file' => 'Le certificat médical doit être un fichier.',
                'medical_certificate.mimes' => 'Le certificat médical doit être au format PDF.',
                'medical_certificate.max' => 'Le certificat médical ne doit pas dépasser 2 Mo.',

                // Souche de chèque
                'check_stub.required' => 'La souche de chèque est obligatoire.',
                'check_stub.file' => 'La souche de chèque doit être un fichier.',
                'check_stub.mimes' => 'La souche de chèque doit être en PDF, JPG ou PNG.',
                'check_stub.max' => 'La souche de chèque ne doit pas dépasser 2 Mo.',
        ];

        $validated = $request->validate([
                'name' => 'required|string|max:255',
                'nif' => 'required|string|max:255',
                'career_certificate' => 'required|file|mimes:pdf|max:2048',
                'monitor_copy' => 'required|file|mimes:pdf|max:2048',
                'marriage_certificate' => 'required|file|mimes:pdf|max:2048',
                'birth_certificate' => 'required|file|mimes:pdf|max:2048',
                'divorce_certificate' => 'nullable|file|mimes:pdf|max:2048',
                'tax_id_number' => 'required|file|mimes:pdf,jpg,png|max:2048',
                'photos' => 'required|array|min:2|max:2',
                'photos.*' => 'image|mimes:jpeg,png,jpg|max:1024',
                'medical_certificate' => 'required|file|mimes:pdf|max:2048',
                'check_stub' => 'required|file|mimes:pdf,jpg,png|max:2048',
        ], $customMessages);


        try{
            $user = Auth::user();

            // Process file uploads
            foreach ([
                'career_certificate',
                'monitor_copy',
                'marriage_certificate',
                'birth_certificate',
                'tax_id_number',
                'medical_certificate',
                'check_stub'
            ] as $field) {
                if ($request->hasFile($field)) {
                    $filePaths[$field] = $request->file($field)->store("pension-requests-docs/{$user->nif}", 'public');
                } else {
                    // Add error logging for missing required files
                    \Log::error("Missing required file upload for field: {$field}");
                    throw new \Exception("Missing required file: {$field}");
                }
            }

            // Process optional 'divorce_certificate' if present
            if ($request->hasFile('divorce_certificate')) {
                $filePaths['divorce_certificate'] = $request->file('divorce_certificate')->store("pension-requests-docs/{$user->nif}", 'public');
            }

            // Generate request code
            $validatedData['code'] = CodeGeneratorService::generateUniqueRequestCode(
                'DEMANDE_PENSION', 
                (new PensionRequest())->getTable()
            );

            // Process photos (multiple)            
            if ($request->hasFile('photos')) {
                foreach ($request->file('photos') as $photo) {
                    $photos[] = $photo->store("pension-requests-docs/{$user->nif}/photos", 'public');
                }
            }

            // System fields
            $validated['status_id'] = Status::getStatusPending()->id;
            $validated['created_by'] = $user->id;

            // Create pension request
            DB::transaction(function () use ($validated, $filePaths, $photos, $user) {
                $pensionRequest = PensionRequest::create([
                    'name' => $validated['name'],
                    'nif' => $validated['nif'],
                    'career_certificate' => $filePaths['career_certificate'],
                    'monitor_copy' => $filePaths['monitor_copy'],
                    'marriage_certificate' => $filePaths['marriage_certificate'],
                    'birth_certificate' => $filePaths['birth_certificate'],
                    'divorce_certificate' => $filePaths['divorce_certificate'] ?? null,
                    'tax_id_number' => $filePaths['tax_id_number'],
                    'photos' => json_encode($photos),
                    'medical_certificate' => $filePaths['medical_certificate'],
                    'check_stub' => $filePaths['check_stub'],
                    'status_id' => Status::getStatusPending()->id,
                    'created_by' => $user->id
                ]);
                    
                
                $requestHistory = RequestHistory::store(
                    $pensionRequest->id,
                    RequestTypeEnum::PENSION_REQUEST,
                    json_encode($pensionRequest),
                    RequestEventTypeEnum::REQUEST_CREATED,
                    $pensionRequest->created_at,
                    auth()->id()
                );
            }); 

            return redirect()->back()
                            ->with('success', 'Demande de pension soumise avec succès.');
        }catch (PostTooLargeException $e) {
            \Log::error('Pension request submission error: ' . $e->getMessage()); 
            Helpers::cleanupFiles(array_merge($filePaths, $photos)); 
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->withErrors([
                    'system_error' => 'La taille totale des fichiers dépasse la limite autorisée de 20 Mo'
                ]);
        }catch(\Exception $e){
            \Log::error('Pension request submission error: ' . $e->getMessage());  
            Helpers::cleanupFiles(array_merge($filePaths, $photos)); 
            DB::rollBack();
            return redirect()->back()
                        ->withInput()
                        ->with('error', 'Une erreur est survenue lors du traitement de votre demande. Veuillez réessayer.');
        }
    }
}
