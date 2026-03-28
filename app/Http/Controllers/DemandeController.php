<?php

namespace App\Http\Controllers;

use App\Enums\DemandeStatusEnum;
use App\Enums\TypeDemandeEnum;
use App\Helpers\CodeGeneratorService;
use App\Http\Requests\StoreArretPaiementRequest;
use App\Http\Requests\StoreDemandeAdhesionRequest;
use App\Http\Requests\StoreDemandeArretVirementRequest;
use App\Http\Requests\StoreDemandeAttestationRequest;
use App\Http\Requests\StoreDemandePensionRequest;
use App\Http\Requests\StoreDemandePensionPensionnaireRequest;
use App\Http\Requests\StoreDemandePensionReversionRequest;
use App\Http\Requests\StoreDemandeReinsertionRequest;
use App\Http\Requests\StoreDemandeVirementBancaireRequest;
use App\Http\Requests\StoreEtatCarriereRequest;
use App\Http\Requests\StorePreuveExistenceRequest;
use App\Http\Requests\StoreTransfertChequeRequest;
use App\Models\CivilStatus;
use App\Models\Demande;
use App\Models\DemandeHistory;
use App\Models\Gender;
use App\Models\PensionCategory;
use App\Models\PensionType;
use App\Models\Service;
use App\Models\Status;
use App\Services\DemandeService;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Log;

class DemandeController extends Controller
{
    protected DemandeService $demandeService;
    
    public function __construct(DemandeService $demandeService)
    {
        $this->demandeService = $demandeService;
    }   
    
    // DEMANDES DE VIREMENTS BANCAIRES
    public function createDemandeVirement($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $pensionTypes = PensionType::orderBy('name', 'asc')->get();
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);
            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );
            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.demande-virement-bancaire', compact('genders', 'civilStatuses', 'pensionTypes', 'pensionCategories', 'demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeVirement(StoreDemandeVirementBancaireRequest $request)
    {
        $validated = $request->validated();

        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with('status')->findOrFail($request->demande_id);
                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code', 'profile_photo',
                        ])->toArray();
                        $demande = Demande::create([
                            'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value, (new Demande())->getTable()),
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'title'      => $request->title,
                            'expires_at' => now()->addYear(),
                        ]);
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load('status');
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            try {
                DB::transaction(function () use ($demande, $request) {
                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', Service::DIRECTION)->value('id');

                    if (! $serviceId) throw new \Exception('Service direction introuvable');

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);
                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'   => null,
                        'to_service_id'     => $demande->current_service_id,
                        'status_id'         => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'       => 'Soumission de la demande',
                    ]);
                });

                return redirect()->route('personal.index')->with('success', 'Demande soumise avec succès.');

            } catch (\Throwable $e) {
                Log::error('Erreur soumission demande virement', ['error' => $e->getMessage()]);
                return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
            }
        }

        // SAVE DRAFT
        $storedFilePaths = [];

        try {
            $demande = DB::transaction(function () use ($request, $validated, &$storedFilePaths) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                $existing = $request->demande_id ? Demande::with('status')->findOrFail($request->demande_id) : null;

                $photoPath = $existing?->data['profile_photo'] ?? null;

                if ($request->hasFile('profile_photo')) {
                    $path = $request->file('profile_photo')->store('demandes/virement-bancaire/' . now()->format('Y/m'), 'public');
                    $storedFilePaths[] = $path;
                    $photoPath = $path;
                }

                $data = collect($validated)->except(['title', 'action', 'demande_id', 'consentement', 'profile_photo'])->toArray();
                if ($photoPath) $data['profile_photo'] = $photoPath;

                if ($existing) {
                    $updateFields = ['data' => $data, 'title' => $request->title];
                    if ($existing->status->code === DemandeStatusEnum::BROUILLON->value) {
                        $updateFields['status_id'] = $draftStatusId;
                    }
                    $existing->update($updateFields);
                    $demande = $existing;
                } else {
                    $demande = Demande::create([
                        'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value, (new Demande())->getTable()),
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                        'title'      => $request->title,
                    ]);
                }

                if (! $demande->expires_at) $demande->update(['expires_at' => now()->addYear()]);

                return $demande;
            });

            return redirect()->route('demandes.virements.create', $demande->id)->with('success', 'Brouillon sauvegardé.');

        } catch (\Throwable $e) {
            if ($storedFilePaths) Storage::disk('public')->delete($storedFilePaths);
            Log::error('Erreur brouillon virement bancaire', ['error' => $e->getMessage()]);
            return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
        }
    }

    // DEMANDES D'ATTESTATION
    public function createDemandeAttestation($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);
            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );
            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.demande-attestation', compact('demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeAttestation(StoreDemandeAttestationRequest $request)
    {
        $validated = $request->validated();

        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with('status')->findOrFail($request->demande_id);
                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create([
                            'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_ATTESTATION->value, (new Demande())->getTable()),
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'title'      => $request->title,
                            'expires_at' => now()->addYear(),
                        ]);
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load('status');
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            try {
                DB::transaction(function () use ($demande, $request) {
                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', Service::DIRECTION)->value('id');

                    if (! $serviceId) throw new \Exception('Service direction introuvable');

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);
                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'   => null,
                        'to_service_id'     => $demande->current_service_id,
                        'status_id'         => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'       => 'Soumission de la demande',
                    ]);
                });

                return redirect()->route('personal.index')->with('success', 'Demande soumise avec succès.');

            } catch (\Throwable $e) {
                Log::error('Erreur soumission attestation', ['error' => $e->getMessage()]);
                return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
            }
        }

        // SAVE DRAFT
        try {
            $demande = DB::transaction(function () use ($request, $validated) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                $data = collect($validated)->except(['title', 'action', 'demande_id', 'consentement'])->toArray();

                if ($request->demande_id) {
                    $demande = Demande::with('status')->findOrFail($request->demande_id);
                    $updateFields = ['data' => $data, 'title' => $request->title];
                    if ($demande->status->code === DemandeStatusEnum::BROUILLON->value) {
                        $updateFields['status_id'] = $draftStatusId;
                    }
                    $demande->update($updateFields);
                } else {
                    $demande = Demande::create([
                        'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_ATTESTATION->value, (new Demande())->getTable()),
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_ATTESTATION->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                        'title'      => $request->title,
                    ]);
                }

                if (! $demande->expires_at) $demande->update(['expires_at' => now()->addYear()]);

                return $demande;
            });

            return redirect()->route('demandes.attestations.create', $demande->id)->with('success', 'Brouillon sauvegardé.');

        } catch (\Throwable $e) {
            Log::error('Erreur brouillon attestation', ['error' => $e->getMessage()]);
            return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
        }
    }

    // DEMANDES DE TRANSFERT DE CHEQUES
    public function createDemandeTransfertCheque($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);
            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );
            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.demande-transfert-cheque', compact('pensionCategories', 'demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeTransfertCheque(StoreTransfertChequeRequest $request)
    {
        $validated = $request->validated();

        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with('status')->findOrFail($request->demande_id);
                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create([
                            'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value, (new Demande())->getTable()),
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'title'      => $request->title,
                            'expires_at' => now()->addYear(),
                        ]);
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load('status');
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            try {
                DB::transaction(function () use ($demande, $request) {
                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', Service::DIRECTION)->value('id');

                    if (! $serviceId) throw new \Exception('Service direction introuvable');

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);
                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'   => null,
                        'to_service_id'     => $demande->current_service_id,
                        'status_id'         => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'       => 'Soumission de la demande',
                    ]);
                });

                return redirect()->route('personal.index')->with('success', 'Demande soumise avec succès.');

            } catch (\Throwable $e) {
                Log::error('Erreur soumission transfert chèque', ['error' => $e->getMessage()]);
                return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
            }
        }

        // SAVE DRAFT
        try {
            $demande = DB::transaction(function () use ($request, $validated) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                $data = collect($validated)->except(['title', 'action', 'demande_id', 'consentement'])->toArray();

                if ($request->demande_id) {
                    $demande = Demande::with('status')->findOrFail($request->demande_id);
                    $updateFields = ['data' => $data, 'title' => $request->title];
                    if ($demande->status->code === DemandeStatusEnum::BROUILLON->value) {
                        $updateFields['status_id'] = $draftStatusId;
                    }
                    $demande->update($updateFields);
                } else {
                    $demande = Demande::create([
                        'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value, (new Demande())->getTable()),
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_TRANSFERT_CHEQUE->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                        'title'      => $request->title,
                    ]);
                }

                if (! $demande->expires_at) $demande->update(['expires_at' => now()->addYear()]);

                return $demande;
            });

            return redirect()->route('demandes.transfert-cheque.create', $demande->id)->with('success', 'Brouillon sauvegardé.');

        } catch (\Throwable $e) {
            Log::error('Erreur brouillon transfert chèque', ['error' => $e->getMessage()]);
            return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
        }
    }

    // DEMANDES D'ARRET DE PAIEMENT
    public function createDemandeArretPaiement($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);
            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );
            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.demande-arret-paiement', compact('pensionCategories', 'demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeArretPaiement(StoreArretPaiementRequest $request)
    {
        $validated = $request->validated();

        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with('status')->findOrFail($request->demande_id);
                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create([
                            'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value, (new Demande())->getTable()),
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'title'      => $request->title,
                            'expires_at' => now()->addYear(),
                        ]);
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load('status');
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            try {
                DB::transaction(function () use ($demande, $request) {
                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', Service::DIRECTION)->value('id');

                    if (! $serviceId) throw new \Exception('Service direction introuvable');

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);
                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'   => null,
                        'to_service_id'     => $demande->current_service_id,
                        'status_id'         => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'       => 'Soumission de la demande',
                    ]);
                });

                return redirect()->route('personal.index')->with('success', 'Demande soumise avec succès.');

            } catch (\Throwable $e) {
                Log::error('Erreur soumission arrêt paiement', ['error' => $e->getMessage()]);
                return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
            }
        }

        // SAVE DRAFT
        $storedFilePaths = [];

        try {
            $demande = DB::transaction(function () use ($request, $validated, &$storedFilePaths) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                $existing = $request->demande_id ? Demande::with('status')->findOrFail($request->demande_id) : null;

                $existingPieces = $existing?->data['pieces'] ?? [];
                $uploadedFiles  = $existingPieces;

                if ($request->hasFile('pieces')) {
                    $basePath = 'demandes/arret-paiement/' . now()->format('Y/m');
                    $uploadedFiles = [];
                    foreach ($request->file('pieces') as $file) {
                        $path = $file->store($basePath, 'public');
                        $storedFilePaths[] = $path;
                        $uploadedFiles[] = $path;
                    }
                }

                $data = collect($validated)->except(['title', 'action', 'demande_id', 'consentement', 'pieces'])->toArray();
                $data['pieces'] = $uploadedFiles;

                if ($existing) {
                    $updateFields = ['data' => $data, 'title' => $request->title];
                    if ($existing->status->code === DemandeStatusEnum::BROUILLON->value) {
                        $updateFields['status_id'] = $draftStatusId;
                    }
                    $existing->update($updateFields);
                    $demande = $existing;
                } else {
                    $demande = Demande::create([
                        'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value, (new Demande())->getTable()),
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_ARRET_PAIEMENT->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                        'title'      => $request->title,
                    ]);
                }

                if (! $demande->expires_at) $demande->update(['expires_at' => now()->addYear()]);

                return $demande;
            });

            return redirect()->route('demandes.arret-paiement.create', $demande->id)->with('success', 'Brouillon sauvegardé.');

        } catch (\Throwable $e) {
            if ($storedFilePaths) Storage::disk('public')->delete($storedFilePaths);
            Log::error('Erreur brouillon arrêt paiement', ['error' => $e->getMessage()]);
            return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
        }
    }



    // DEMANDES DE REINSERTION
    public function createDemandeReinsertion($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);
            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );
            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.demande-reinsertion', compact('demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeReinsertion(StoreDemandeReinsertionRequest $request)
    {
        $validated = $request->validated();

        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with('status')->findOrFail($request->demande_id);
                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create([
                            'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_REINSERTION->value, (new Demande())->getTable()),
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_REINSERTION->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'title'      => $request->title,
                            'expires_at' => now()->addYear(),
                        ]);
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load('status');
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            try {
                DB::transaction(function () use ($demande, $request) {
                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', Service::DIRECTION)->value('id');

                    if (! $serviceId) throw new \Exception('Service direction introuvable');

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);
                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'   => null,
                        'to_service_id'     => $demande->current_service_id,
                        'status_id'         => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'       => 'Soumission de la demande',
                    ]);
                });

                return redirect()->route('personal.index')->with('success', 'Demande soumise avec succès.');

            } catch (\Throwable $e) {
                Log::error('Erreur soumission réinsertion', ['error' => $e->getMessage()]);
                return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
            }
        }

        // SAVE DRAFT
        try {
            $demande = DB::transaction(function () use ($request, $validated) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                $data = collect($validated)->except(['title', 'action', 'demande_id'])->toArray();

                if ($request->demande_id) {
                    $demande = Demande::with('status')->findOrFail($request->demande_id);
                    $updateFields = ['data' => $data, 'title' => $request->title];
                    if ($demande->status->code === DemandeStatusEnum::BROUILLON->value) {
                        $updateFields['status_id'] = $draftStatusId;
                    }
                    $demande->update($updateFields);
                } else {
                    $demande = Demande::create([
                        'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_REINSERTION->value, (new Demande())->getTable()),
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_REINSERTION->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                        'title'      => $request->title,
                    ]);
                }

                if (! $demande->expires_at) $demande->update(['expires_at' => now()->addYear()]);

                return $demande;
            });

            return redirect()->route('demandes.demande-reinsertion.create', $demande->id)->with('success', 'Brouillon sauvegardé.');

        } catch (\Throwable $e) {
            Log::error('Erreur brouillon réinsertion', ['error' => $e->getMessage()]);
            return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
        }
    }

    // DEMANDES ARRET VIREMENT
    public function createDemandeArretVirement($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);
            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );
            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.demande-arret-virement', compact('demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeArretVirement(StoreDemandeArretVirementRequest $request)
    {
        $validated = $request->validated();

        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with('status')->findOrFail($request->demande_id);
                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create([
                            'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value, (new Demande())->getTable()),
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'title'      => $request->title,
                            'expires_at' => now()->addYear(),
                        ]);
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load('status');
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            try {
                DB::transaction(function () use ($demande, $request) {
                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', Service::DIRECTION)->value('id');

                    if (! $serviceId) throw new \Exception('Service direction introuvable');

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);
                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'   => null,
                        'to_service_id'     => $demande->current_service_id,
                        'status_id'         => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'       => 'Soumission de la demande',
                    ]);
                });

                return redirect()->route('personal.index')->with('success', 'Demande soumise avec succès.');

            } catch (\Throwable $e) {
                Log::error('Erreur soumission arrêt virement', ['error' => $e->getMessage()]);
                return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
            }
        }

        // SAVE DRAFT
        try {
            $demande = DB::transaction(function () use ($request, $validated) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                $data = collect($validated)->except(['title', 'action', 'demande_id', 'consentement'])->toArray();

                if ($request->demande_id) {
                    $demande = Demande::with('status')->findOrFail($request->demande_id);
                    $updateFields = ['data' => $data, 'title' => $request->title];
                    if ($demande->status->code === DemandeStatusEnum::BROUILLON->value) {
                        $updateFields['status_id'] = $draftStatusId;
                    }
                    $demande->update($updateFields);
                } else {
                    $demande = Demande::create([
                        'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value, (new Demande())->getTable()),
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_ARRET_VIREMENT->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                        'title'      => $request->title,
                    ]);
                }

                if (! $demande->expires_at) $demande->update(['expires_at' => now()->addYear()]);

                return $demande;
            });

            return redirect()->route('demandes.demande-arret-virement.create', $demande->id)->with('success', 'Brouillon sauvegardé.');

        } catch (\Throwable $e) {
            Log::error('Erreur brouillon arrêt virement', ['error' => $e->getMessage()]);
            return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
        }
    }

    // PREUVE EXISTENCE
    public function createPreuveExistence($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $pensionCategories = PensionCategory::orderBy('name', 'asc')->get();

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);
            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );
            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.preuve-existence', compact('genders', 'civilStatuses', 'pensionCategories', 'demande', 'isDemandeReadyForSubmission'));
    }

    public function storePreuveExistence(StorePreuveExistenceRequest $request)
    {
        $validated = $request->validated();

        if ($request->action === 'submit') {
            $demande = Demande::with('status')->findOrFail($request->demande_id);

            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403
            );

            try {
                DB::transaction(function () use ($demande, $request) {
                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', Service::DIRECTION)->value('id');

                    if (! $serviceId) throw new \Exception('Service direction introuvable');

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);
                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'   => null,
                        'to_service_id'     => $demande->current_service_id,
                        'status_id'         => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'       => 'Soumission de la demande',
                    ]);
                });

                return redirect()->route('personal.index')->with('success', 'Demande soumise avec succès.');

            } catch (\Throwable $e) {
                Log::error('Erreur soumission preuve existence', ['error' => $e->getMessage()]);
                return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
            }
        }

        // SAVE DRAFT
        $storedFilePaths = [];

        try {
            $demande = DB::transaction(function () use ($request, $validated, &$storedFilePaths) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                $existing = $request->demande_id ? Demande::with('status')->findOrFail($request->demande_id) : null;

                $photoPath = $existing?->data['documents']['profile_photo'] ?? null;

                if ($request->hasFile('profile_photo')) {
                    $path = $request->file('profile_photo')->store('demandes/preuve-existence/' . now()->format('Y/m'), 'public');
                    $storedFilePaths[] = $path;
                    $photoPath = $path;
                }

                $data = collect($validated)->except(['title', 'action', 'demande_id', 'profile_photo'])->toArray();
                $data['documents'] = $photoPath ? ['profile_photo' => $photoPath] : [];

                if ($existing) {
                    $updateFields = ['data' => $data, 'title' => $request->title];
                    if ($existing->status->code === DemandeStatusEnum::BROUILLON->value) {
                        $updateFields['status_id'] = $draftStatusId;
                    }
                    $existing->update($updateFields);
                    $demande = $existing;
                } else {
                    $demande = Demande::create([
                        'code'       => CodeGeneratorService::generateUniqueRequestCode(TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value, (new Demande())->getTable()),
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_PREUVE_EXISTENCE->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                        'title'      => $request->title,
                    ]);
                }

                if (! $demande->expires_at) $demande->update(['expires_at' => now()->addYear()]);

                return $demande;
            });

            return redirect()->route('demandes.preuve-existence.create', $demande->id)->with('success', 'Brouillon sauvegardé.');

        } catch (\Throwable $e) {
            if ($storedFilePaths) Storage::disk('public')->delete($storedFilePaths);
            Log::error('Erreur brouillon preuve existence', ['error' => $e->getMessage()]);
            return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
        }
    }

    // DEMANDE ETAT CARRIERE
    public function createDemandeEtatCarriere($demandeId = null){
        $demande = null;
        $isDemandeReadyForSubmission = false;
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();   

        if ($demandeId) {
            $demande = Demande::with('documents')->findOrFail($demandeId);

            abort_if(
                $demande->user->id !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );

            $isDemandeReadyForSubmission = $this->demandeService->isDemandeReadyForSubmission($demande);
        }

        return view('fonctionnaire.etat-carriere', compact('civilStatuses', 'demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeEtatCarriere(StoreEtatCarriereRequest $request){
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | SUBMIT DEMANDE
        |--------------------------------------------------------------------------
        */
        if ($request->action === 'submit') {
            $demande = Demande::with(['documents', 'status'])->findOrFail($request->demande_id);
            

            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'Vous n\'êtes pas autorisé à modifier cette demande.'
            );

            $validationErrors = $this->demandeService->validateDocumentsForSubmission($demande);

            if (!empty($validationErrors)) {
                return back()->withErrors(['documents' => $validationErrors]);
            }

            try {
                DB::transaction(function () use ($demande, $request) {

                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', 'direction')->value('id');

                    if (!$serviceId) {
                        throw new \Exception('Service direction introuvable');
                    }

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);

                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id' => null,
                        'to_service_id'   => $demande->current_service_id,
                        'status_id'       => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'     => 'Soumission de la demande',
                    ]);
                });

                return redirect()
                    ->route('personal.index')
                    ->with('success', 'Demande soumise avec succès.');

            } catch (\Exception $e) {

                Log::error('Erreur lors de la soumission', [
                    'demande_id' => $demande->id,
                    'error'      => $e->getMessage(),
                ]);

                return back()
                    ->with('error', 'Une erreur inattendue est survenue.')
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE DRAFT (BROUILLON)
        |--------------------------------------------------------------------------
        */
        try {
            $demande = DB::transaction(function () use ($request, $validated) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                if ($request->demande_id) {
                    $existing = Demande::find($request->demande_id);
                    if ($existing?->needsComplement()) {
                        $draftStatusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
                    }
                } else {
                    $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                        TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value,
                        (new Demande())->getTable()
                    );
                }

                $data = collect($validated)->except([
                    'title',
                    'action',
                    'demande_id',
                    'status_id',
                    'created_by',
                    'type',
                    'current_service_id',
                    'code'
                ])->toArray();

                $demande = Demande::updateOrCreate(
                    ['id' => $request->demande_id],
                    array_merge($validated, [
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_ETAT_CARRIERE->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                    ])
                );

                if (!$demande->expires_at) {
                    $demande->update([
                        'expires_at' => now()->addYear(),
                    ]);
                }

                

                $this->demandeService->storeFiles($demande, $request->allFiles());

                return $demande;
            });

            return redirect()
                ->route('demandes.demande-etat-carriere.create', $demande->id)
                ->with('success', 'Demande sauvegardée en brouillon.');

        } catch (\Exception $e) {
            dd($e);
            Log::error('Erreur lors de la sauvegarde de la demande', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

/*     public function storeDemandeEtatCarriere(StoreEtatCarriereRequest $request)
    {
        // 👉 Tableau pour tracer tous les fichiers stockés
        $storedFilePaths = [];

        try {
            DB::transaction(function () use ($request, &$storedFilePaths) {
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
                $serviceId = Service::where('code', Service::DIRECTION)
                                    ->value('id');
                if (! $serviceId) {
                    throw new \Exception('Service secrétariat introuvable');
                }
                $validated['current_service_id'] = $serviceId;

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
                        'consentement',
                        'status_id',
                        'created_by',
                        'type',
                        'current_service_id',
                        'code',
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
                    'statut' => $demande->status->code,
                    'commentaire' => 'Demande créée',
                    'changed_by' => auth()->id(),
                    'data' => $demande->data
                ]);

                $demande->workflows()->create([
                    'from_service_id' => null,
                    'to_service_id'   => $demande->current_service_id,
                    'status_id'       => $demande->status->id,
                    'action_by_user_id' => auth()->id(),
                    'commentaire'     => 'Soumission de la demande',
                ]);
            });

           return back()->with('success', 'Demande enregistrée avec succès.');
        } catch (ValidationException $e) {

            // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }

            return back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            // ❌ Supprimer les fichiers stockés
            if (!empty($storedFilePaths)) {
                Storage::disk('public')->delete($storedFilePaths);
            }
            Log::error($e);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    } */

    // DEMANDE DE PENSION STANDARD
    public function showDemandesPensionPage(){
        return view('institution.demande-pension');
    }

    public function createDemandePensionStandard($demandeId = null){
        $demande = null;
        $isDemandeReadyForSubmission = false;

        if ($demandeId) {
            $demande = Demande::with('documents')->findOrFail($demandeId);

            abort_if(
                $demande->user->id !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );

            $isDemandeReadyForSubmission = $this->demandeService->isDemandeReadyForSubmission($demande);
        }

       return view('institution.demande-pension-standard', compact('demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandePensionStandard(StoreDemandePensionRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | SUBMIT DEMANDE
        |--------------------------------------------------------------------------
        */
        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with(['documents', 'status'])
                    ->findOrFail($request->demande_id);

                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403,
                    'Vous n\'êtes pas autorisé à modifier cette demande.'
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                            TypeDemandeEnum::DEMANDE_PENSION->value,
                            (new Demande())->getTable()
                        );
                        $demande = Demande::create(array_merge($validated, [
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_PENSION->value,
                            'status_id'  => $draftStatusId,
                            'expires_at' => now()->addYear(),
                        ]));
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load(['documents', 'status']);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            $validationErrors = $this->demandeService
                ->validateDocumentsForSubmission($demande);

            if (!empty($validationErrors)) {
                return redirect()
                    ->route('demandes.demande-pension-standard.create', $demande->id)
                    ->withErrors(['documents' => $validationErrors]);
            }

            try {
                DB::transaction(function () use ($demande, $request) {

                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', 'direction')->value('id');

                    if (!$serviceId) {
                        throw new \Exception('Service direction introuvable');
                    }

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);

                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id' => null,
                        'to_service_id'   => $demande->current_service_id,
                        'status_id'       => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'     => 'Soumission de la demande',
                    ]);
                });

                return redirect()
                    ->route('personal.index')
                    ->with('success', 'Demande soumise avec succès.');

            } catch (\Exception $e) {

                Log::error('Erreur lors de la soumission', [
                    'demande_id' => $demande->id,
                    'error'      => $e->getMessage(),
                ]);

                return back()
                    ->with('error', 'Une erreur inattendue est survenue.')
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE DRAFT (BROUILLON)
        |--------------------------------------------------------------------------
        */
        try {
            $demande = DB::transaction(function () use ($request, $validated) {

                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                if ($request->demande_id) {
                    $existing = Demande::find($request->demande_id);
                    if ($existing?->needsComplement()) {
                        $draftStatusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
                    }
                } else {
                    $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                        TypeDemandeEnum::DEMANDE_PENSION->value,
                        (new Demande())->getTable()
                    );
                }

                $demande = Demande::updateOrCreate(
                    ['id' => $request->demande_id],
                    array_merge($validated, [
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_PENSION->value,
                        'status_id'  => $draftStatusId,
                    ])
                );

                if (!$demande->expires_at) {
                    $demande->update([
                        'expires_at' => now()->addYear(),
                    ]);
                }

                $this->demandeService
                    ->storeFiles($demande, $request->allFiles());

                return $demande;
            });

            return redirect()
                ->route('demandes.demande-pension-standard.create', $demande->id)
                ->with('success', 'Demande sauvegardée en brouillon.');

        } catch (\Exception $e) {
            Log::error('Erreur lors de la sauvegarde de la demande', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDE DE PENSION DE REVERSION
    public function createDemandePensionReversion($demandeId = null){
        $demande = null;
        $isDemandeReadyForSubmission = false;

        if ($demandeId) {
            $demande = Demande::with('documents')->findOrFail($demandeId);

            abort_if(
                $demande->user->id !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );

            $isDemandeReadyForSubmission = $this->demandeService->isDemandeReadyForSubmission($demande);
        }

       return view('institution.demande-pension-reversion', compact('demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandePensionReversion(StoreDemandePensionReversionRequest $request){

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | SUBMIT DEMANDE
        |--------------------------------------------------------------------------
        */
        if ($request->action === 'submit') {

            if ($request->demande_id) {
                $demande = Demande::with(['documents', 'status'])
                    ->findOrFail($request->demande_id);

                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403,
                    'Vous n\'êtes pas autorisé à modifier cette demande.'
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                            TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                            (new Demande())->getTable()
                        );
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create(array_merge($validated, [
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'expires_at' => now()->addYear(),
                        ]));
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load(['documents', 'status']);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            $validationErrors = $this->demandeService
                ->validateDocumentsForSubmission($demande);

            if (!empty($validationErrors)) {
                return redirect()
                    ->route('demandes.demande-pension-reversion.create', $demande->id)
                    ->withErrors(['documents' => $validationErrors]);
            }

            try {
                DB::transaction(function () use ($demande, $request) {

                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', 'direction')->value('id');

                    if (!$serviceId) {
                        throw new \Exception('Service direction introuvable');
                    }

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);

                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id' => null,
                        'to_service_id'   => $demande->current_service_id,
                        'status_id'       => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'     => 'Soumission de la demande',
                    ]);
                });

                return redirect()
                    ->route('personal.index')
                    ->with('success', 'Demande soumise avec succès.');

            } catch (\Exception $e) {

                Log::error('Erreur lors de la soumission', [
                    'demande_id' => $demande->id,
                    'error'      => $e->getMessage(),
                ]);

                return back()
                    ->with('error', 'Une erreur inattendue est survenue.')
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE DRAFT (BROUILLON)
        |--------------------------------------------------------------------------
        */
        try {
            $demande = DB::transaction(function () use ($request, $validated) {

                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                if ($request->demande_id) {
                    $existing = Demande::find($request->demande_id);
                    if ($existing?->needsComplement()) {
                        $draftStatusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
                    }
                } else {
                    $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                        TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                        (new Demande())->getTable()
                    );
                }

                $data = collect($validated)->except([
                    'title',
                    'action',
                    'demande_id',
                    'status_id',
                    'created_by',
                    'type',
                    'current_service_id',
                    'code'
                ])->toArray();

                $demande = Demande::updateOrCreate(
                    ['id' => $request->demande_id],
                    array_merge($validated, [
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                        'status_id'  => $draftStatusId,
                        'data' => $data
                    ])
                );

                if (!$demande->expires_at) {
                    $demande->update([
                        'expires_at' => now()->addYear(),
                    ]);
                }

                $this->demandeService
                    ->storeFiles($demande, $request->allFiles());

                return $demande;
            });

            return redirect()
                ->route('demandes.demande-pension-reversion.create', $demande->id)
                ->with('success', 'Demande sauvegardée en brouillon.');

        } catch (\Exception $e) {

            Log::error('Erreur lors de la sauvegarde de la demande', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // DEMANDE D'ADHESION
    public function createDemandeAdhesion($demandeId = null){
        $genders = Gender::orderBy('name', 'asc')->get();
        $civilStatuses = CivilStatus::orderBy('name', 'asc')->get();
        $demande = null;
        $isDemandeReadyForSubmission = false;

        if ($demandeId) {
            $demande = Demande::with('documents')->findOrFail($demandeId);

            abort_if(
                $demande->user->id !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );

            $isDemandeReadyForSubmission = $this->demandeService->isDemandeReadyForSubmission($demande);
        }

        return view('institution.demande-adhesion', compact('genders', 'civilStatuses', 'demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandeAdhesion(StoreDemandeAdhesionRequest $request){
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | SUBMIT DEMANDE
        |--------------------------------------------------------------------------
        */
        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with(['documents', 'status'])->findOrFail($request->demande_id);

                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403,
                    'Vous n\'êtes pas autorisé à modifier cette demande.'
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                            TypeDemandeEnum::DEMANDE_ADHESION->value,
                            (new Demande())->getTable()
                        );
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'profile_photo', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create(array_merge($validated, [
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_ADHESION->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'expires_at' => now()->addYear(),
                        ]));
                        $this->demandeService->storeFiles($demande, $request->allFiles());
                        return $demande;
                    });
                    $demande->load(['documents', 'status']);
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            $validationErrors = $this->demandeService->validateDocumentsForSubmission($demande);

            if (!empty($validationErrors)) {
                return redirect()
                    ->route('demandes.demande-adhesion.create', $demande->id)
                    ->withErrors(['documents' => $validationErrors]);
            }

            try {
                DB::transaction(function () use ($demande, $request) {

                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', 'direction')->value('id');

                    if (!$serviceId) {
                        throw new \Exception('Service direction introuvable');
                    }

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);

                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id' => null,
                        'to_service_id'   => $demande->current_service_id,
                        'status_id'       => $demande->status->id,
                        'action_by_user_id' => auth()->id(),
                        'commentaire'     => 'Soumission de la demande',
                    ]);
                });

                return redirect()
                    ->route('personal.index')
                    ->with('success', 'Demande soumise avec succès.');

            } catch (\Exception $e) {

                Log::error('Erreur lors de la soumission', [
                    'demande_id' => $demande->id,
                    'error'      => $e->getMessage(),
                ]);

                return back()
                    ->with('error', 'Une erreur inattendue est survenue.')
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE DRAFT (BROUILLON)
        |--------------------------------------------------------------------------
        */
        try {
            $demande = DB::transaction(function () use ($request, $validated) {
                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                if ($request->demande_id) {
                    $existing = Demande::find($request->demande_id);
                    if ($existing?->needsComplement()) {
                        $draftStatusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
                    }
                } else {
                    $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                        TypeDemandeEnum::DEMANDE_ADHESION->value,
                        (new Demande())->getTable()
                    );
                }

                $data = collect($validated)->except([
                    'title',
                    'action',
                    'demande_id',
                    'profile_photo',
                    'status_id',
                    'created_by',
                    'type',
                    'current_service_id',
                    'code'
                ])->toArray();

                $demande = Demande::updateOrCreate(
                    ['id' => $request->demande_id],
                    array_merge($validated, [
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_ADHESION->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                    ])
                );

                if (!$demande->expires_at) {
                    $demande->update([
                        'expires_at' => now()->addYear(),
                    ]);
                }

                $this->demandeService->storeFiles($demande, $request->allFiles());

                return $demande;
            });

            return redirect()
                ->route('demandes.demande-adhesion.create', $demande->id)
                ->with('success', 'Demande sauvegardée en brouillon.');

        } catch (\Exception $e) {

            Log::error('Erreur lors de la sauvegarde de la demande', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }

    // SUPPRIMER DEMANDE
    public function destroyDemande(Demande $demande)
    {
        if (
            $demande->status->code !== 'BROUILLON' ||
            $demande->user->id !== auth()->id()
        ) {
            abort(403, 'Vous n\'êtes pas autorisé à supprimer cette demande.');
        }

        $demande->load('documents');

        // Delete files stored via DemandeDocument model
        $parentDirectory = null;
        foreach ($demande->documents as $document) {
            if ($document->path) {
                if (! $parentDirectory) {
                    $parentDirectory = dirname($document->path);
                }
                Storage::disk('public')->delete($document->path);
            }
        }
        if ($parentDirectory && empty(Storage::disk('public')->files($parentDirectory))) {
            Storage::disk('public')->deleteDirectory($parentDirectory);
        }

        // Delete files stored directly in the data JSON column
        $data = $demande->data ?? [];
        $dataFiles = array_filter([
            $data['profile_photo'] ?? null,
            $data['documents']['profile_photo'] ?? null,
        ]);
        foreach ($data['pieces'] ?? [] as $piece) {
            $dataFiles[] = $piece;
        }
        if ($dataFiles) {
            Storage::disk('public')->delete(array_values($dataFiles));
        }

        DB::transaction(function () use ($demande) {
            $demande->documents()->delete();
            $demande->delete();
        });

        return redirect()
            ->route('personal.index')
            ->with('success', 'Demande supprimée avec succès.');
    }

    // DEMANDE DE PENSION (PENSIONNAIRE)
    public function createDemandePensionPensionnaire($demandeId = null)
    {
        $demande = null;
        $isDemandeReadyForSubmission = false;

        if ($demandeId) {
            $demande = Demande::findOrFail($demandeId);

            abort_if(
                $demande->created_by !== auth()->id() ||
                !$demande->canBeEditedByUser(),
                403,
                'La demande ne peut pas être modifiée !'
            );

            $isDemandeReadyForSubmission = !empty($demande->data);
        }

        return view('pensionnaire.demande_pension', compact('demande', 'isDemandeReadyForSubmission'));
    }

    public function storeDemandePensionPensionnaire(StoreDemandePensionPensionnaireRequest $request)
    {
        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | SUBMIT DEMANDE
        |--------------------------------------------------------------------------
        */
        if ($request->action === 'submit') {
            if ($request->demande_id) {
                $demande = Demande::with('status')->findOrFail($request->demande_id);

                abort_if(
                    $demande->created_by !== auth()->id() ||
                    !$demande->canBeEditedByUser(),
                    403,
                    'Vous n\'êtes pas autorisé à modifier cette demande.'
                );
            } else {
                // First submission — create draft then submit in one shot
                try {
                    $demande = DB::transaction(function () use ($request, $validated) {
                        $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');
                        $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                            TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                            (new Demande())->getTable()
                        );
                        $data = collect($validated)->except([
                            'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                        ])->toArray();
                        $demande = Demande::create(array_merge($validated, [
                            'created_by' => auth()->id(),
                            'type'       => TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                            'status_id'  => $draftStatusId,
                            'data'       => $data,
                            'expires_at' => now()->addYear(),
                        ]));
                        return $demande;
                    });
                    $demande->load('status');
                } catch (\Exception $e) {
                    Log::error('Erreur lors de la soumission', ['error' => $e->getMessage()]);
                    return back()->with('error', 'Une erreur inattendue est survenue.')->withInput();
                }
            }

            try {
                DB::transaction(function () use ($demande, $request) {

                    $statusId  = Status::where('code', DemandeStatusEnum::SOUMISE->value)->value('id');
                    $serviceId = Service::where('code', 'direction')->value('id');

                    if (!$serviceId) {
                        throw new \Exception('Service direction introuvable');
                    }

                    $demande->update([
                        'title'              => $request->title,
                        'status_id'          => $statusId,
                        'current_service_id' => $serviceId,
                        'submitted_at'       => now(),
                        'expires_at'         => null,
                    ]);

                    $demande->refresh();

                    DemandeHistory::create([
                        'demande_id'  => $demande->id,
                        'statut'      => $demande->status->code,
                        'commentaire' => 'Demande soumise',
                        'changed_by'  => auth()->id(),
                        'data'        => $demande->data,
                    ]);

                    $demande->workflows()->create([
                        'from_service_id'    => null,
                        'to_service_id'      => $demande->current_service_id,
                        'status_id'          => $demande->status->id,
                        'action_by_user_id'  => auth()->id(),
                        'commentaire'        => 'Soumission de la demande',
                    ]);
                });

                return redirect()
                    ->route('personal.index')
                    ->with('success', 'Demande soumise avec succès.');

            } catch (\Exception $e) {

                Log::error('Erreur lors de la soumission', [
                    'demande_id' => $demande->id,
                    'error'      => $e->getMessage(),
                ]);

                return back()
                    ->with('error', 'Une erreur inattendue est survenue.')
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | SAVE DRAFT (BROUILLON)
        |--------------------------------------------------------------------------
        */
        try {
            $demande = DB::transaction(function () use ($request, $validated) {

                $draftStatusId = Status::where('code', DemandeStatusEnum::BROUILLON->value)->value('id');

                if ($request->demande_id) {
                    $existing = Demande::find($request->demande_id);
                    if ($existing?->needsComplement()) {
                        $draftStatusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');
                    }
                } else {
                    $validated['code'] = CodeGeneratorService::generateUniqueRequestCode(
                        TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                        (new Demande())->getTable()
                    );
                }

                $data = collect($validated)->except([
                    'title', 'action', 'demande_id', 'status_id', 'created_by', 'type', 'current_service_id', 'code',
                ])->toArray();

                $demande = Demande::updateOrCreate(
                    ['id' => $request->demande_id],
                    array_merge($validated, [
                        'created_by' => auth()->id(),
                        'type'       => TypeDemandeEnum::DEMANDE_PENSION_REVERSION->value,
                        'status_id'  => $draftStatusId,
                        'data'       => $data,
                    ])
                );

                if (!$demande->expires_at) {
                    $demande->update(['expires_at' => now()->addYear()]);
                }

                return $demande;
            });

            return redirect()
                ->route('demandes.pension-pensionnaire.create', $demande->id)
                ->with('success', 'Demande sauvegardée en brouillon.');

        } catch (\Exception $e) {

            Log::error('Erreur lors de la sauvegarde de la demande', [
                'error' => $e->getMessage(),
            ]);

            return back()
                ->with('error', 'Une erreur inattendue est survenue.')
                ->withInput();
        }
    }
}
