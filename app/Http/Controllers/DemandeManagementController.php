<?php


namespace App\Http\Controllers;


use App\Models\Affectation;
use App\Models\Demande;
use App\Models\DemandeActivityLog;
use App\Models\DemandeHistory;
use App\Models\DemandeMessage;
use App\Models\DemandeWorkflow;
use App\Models\FluxTransition;
use App\Models\RequiredCircuitService;
use App\Models\Service;
use App\Models\Status;
use App\Notifications\DemandeStatusChangedNotification;
use App\Notifications\DemandeTransferredNotification;
use App\Services\DemandeWorkflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Requests\TransfertDemandeRequest;

class DemandeManagementController extends Controller
{
    public function index()
    {
        $demandes = Demande::orderBy('created_at', 'desc')->paginate(50);
        return view('demandes.admin.index', compact('demandes'));
    }

    public function edit(Demande $demande)
    {
        DemandeActivityLog::create([
            'demande_id' => $demande->id,
            'user_id'    => auth()->id(),
            'action'     => 'viewed',
        ]);

        return view('demandes.admin.edit', compact('demande'));
    }

    public function updateStatus(Request $request, Demande $demande)
    {
        $request->validate([
            'etat'        => 'required|string',
            'commentaire' => 'nullable|string',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $statusId = Status::where('code', $request->etat)->value('id');
            $demande->update(['status_id' => $statusId]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => $request->etat,
                'commentaire' => $request->commentaire,
                'changed_by'  => auth()->id(),
            ]);
        });

        // Notify the demande owner
        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification(
                    $demande,
                    $request->etat,
                    $request->commentaire,
                ));
            }
        } catch (\Throwable $e) {
            Log::error('updateStatus: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'État mis à jour');
    }

    /**
     * Annotation du dossier par la Direction.
     * Obligatoire avant tout transfert, impression ou téléchargement.
     */
    public function annotate(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403, 'Seule la Direction peut annoter un dossier.');

        $request->validate([
            'annotation' => 'required|string|max:2000',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $demande->update([
                'annotation'   => $request->annotation,
                'annotated_by' => auth()->id(),
                'annotated_at' => now(),
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => $demande->status->code,
                'commentaire' => 'Annotation par la Direction : ' . $request->annotation,
                'changed_by'  => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Dossier annoté avec succès.');
    }

    /**
     * Demande de complément d'information à l'usager.
     * Le dossier passe en statut COMPLEMENT_REQUIS et un message est envoyé.
     */
    public function requestComplement(Request $request, Demande $demande)
    {
        $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        $previousStatusCode = $demande->status->code;

        DB::transaction(function () use ($demande, $request, $previousStatusCode) {
            $complementStatusId = Status::where('code', 'COMPLEMENT_REQUIS')->value('id');

            $demande->update(['status_id' => $complementStatusId]);

            DemandeMessage::create([
                'demande_id' => $demande->id,
                'sender_id'  => auth()->id(),
                'body'       => $request->message,
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => 'COMPLEMENT_REQUIS',
                'commentaire' => 'Complément requis : ' . $request->message,
                'changed_by'  => auth()->id(),
            ]);
        });

        // Notify the demande owner (the observer will also fire, but being explicit here
        // ensures the message is included even if the observer skips it)
        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification(
                    $demande,
                    'COMPLEMENT_REQUIS',
                    $request->message,
                ));
            }
        } catch (\Throwable $e) {
            Log::error('requestComplement: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Demande de complément envoyée à l\'usager.');
    }

    /**
     * Transfert d'un dossier vers un service.
     * Crée un transfert EN ATTENTE — le service destinataire devra confirmer la réception.
     */
    public function transfererDemande(TransfertDemandeRequest $request, DemandeWorkflowService $workflowService)
    {
        $demande = Demande::with('status')->findOrFail($request->demande_id);

        abort_if(
            !$demande->isAnnotated(),
            403,
            'Le dossier doit être annoté par la Direction avant d\'être transféré.'
        );

        $toService = Service::findOrFail($request->service_id);

        $workflowService->transfer($demande, $toService, auth()->user(), $request->commentaire);

        DemandeActivityLog::create([
            'demande_id' => $demande->id,
            'user_id'    => auth()->id(),
            'action'     => 'transferred',
            'metadata'   => [
                'to_service'  => $toService->nom,
                'commentaire' => $request->commentaire,
            ],
        ]);

        // Notify all users assigned to the target service
        try {
            foreach ($toService->users as $user) {
                $user->notify(new DemandeTransferredNotification($demande, $toService, $request->commentaire));
            }
        } catch (\Throwable $e) {
            Log::error('transfererDemande: service notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('personal.cart')->with('success', 'Transfert initié vers ' . $toService->nom . '. En attente de confirmation de réception.');
    }

    /**
     * Le service destinataire confirme la réception du dossier.
     */
    public function accepterReception(Request $request, DemandeWorkflow $workflow, DemandeWorkflowService $workflowService)
    {
        abort_unless(
            auth()->user()->service_id === $workflow->to_service_id || auth()->user()->hasRole('admin'),
            403,
            'Seul le service destinataire peut confirmer la réception.'
        );

        $workflowService->accepterReception($workflow, auth()->user());

        DemandeActivityLog::create([
            'demande_id' => $workflow->demande_id,
            'user_id'    => auth()->id(),
            'action'     => 'reception_accepted',
        ]);

        return redirect()->back()->with('success', 'Réception confirmée. Le dossier est maintenant en cours de traitement.');
    }

    /**
     * Le service destinataire refuse la réception — le dossier repart au service expéditeur.
     */
    public function refuserReception(Request $request, DemandeWorkflow $workflow, DemandeWorkflowService $workflowService)
    {
        $request->validate(['motif' => 'nullable|string|max:1000']);

        abort_unless(
            auth()->user()->service_id === $workflow->to_service_id || auth()->user()->hasRole('admin'),
            403,
            'Seul le service destinataire peut refuser la réception.'
        );

        $workflowService->refuserReception($workflow, auth()->user(), $request->motif);

        DemandeActivityLog::create([
            'demande_id' => $workflow->demande_id,
            'user_id'    => auth()->id(),
            'action'     => 'reception_refused',
            'metadata'   => ['motif' => $request->motif],
        ]);

        return redirect()->back()->with('success', 'Transfert refusé. Le dossier a été retourné au service expéditeur.');
    }

    /**
     * Validation finale du dossier par la Direction.
     */
    /**
     * Un dossier ne peut être finalisé par la Direction que s'il a été
     * préalablement acheminé depuis un autre service (workflow accepté, source non nulle).
     */
    private function hasBeenProcessedByAService(Demande $demande): bool
    {
        return $demande->workflows()
            ->where('reception_status', 'accepted')
            ->whereNotNull('from_service_id')
            ->exists();
    }

    private function missingRequiredServices(Demande $demande): array
    {
        $requiredServiceIds = RequiredCircuitService::where(function ($q) use ($demande) {
            $q->where('type_demande', $demande->type)
              ->orWhereNull('type_demande');
        })->pluck('service_id')->unique();

        if ($requiredServiceIds->isEmpty()) return [];

        $visitedServiceIds = $demande->workflows()
            ->where('reception_status', 'accepted')
            ->whereNotNull('to_service_id')
            ->pluck('to_service_id')
            ->unique();

        return Service::whereIn('id', $requiredServiceIds)
            ->whereNotIn('id', $visitedServiceIds)
            ->pluck('nom')
            ->all();
    }

    public function approuver(Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);

        abort_unless(
            $this->hasBeenProcessedByAService($demande),
            403,
            'Ce dossier doit avoir été traité et acheminé par un autre service avant d\'être approuvé par la Direction.'
        );

        abort_if(
            $demande->affectations()->where('statut', 'EN_ATTENTE')->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant d\'approuver le dossier.'
        );

        $missing = $this->missingRequiredServices($demande);
        abort_if(
            count($missing) > 0,
            403,
            'Le circuit de traitement n\'est pas complet. Services manquants : ' . implode(', ', $missing) . '.'
        );

        $statusId = Status::where('code', Status::STATUS_APPROVED)->value('id');

        DB::transaction(function () use ($demande, $statusId) {
            $demande->update(['status_id' => $statusId]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => Status::STATUS_APPROVED,
                'commentaire' => 'Dossier approuvé par la Direction.',
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, Status::STATUS_APPROVED, null));
            }
        } catch (\Throwable $e) {
            Log::error('approuver: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier approuvé.');
    }

    /**
     * Clôture définitive du dossier par la Direction.
     */
    public function cloturer(Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);

        abort_unless(
            $this->hasBeenProcessedByAService($demande),
            403,
            'Ce dossier doit avoir été traité et acheminé par un autre service avant d\'être clôturé par la Direction.'
        );

        abort_if(
            $demande->affectations()->where('statut', 'EN_ATTENTE')->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant de clôturer le dossier.'
        );

        $missing = $this->missingRequiredServices($demande);
        abort_if(
            count($missing) > 0,
            403,
            'Le circuit de traitement n\'est pas complet. Services manquants : ' . implode(', ', $missing) . '.'
        );

        $statusId = Status::where('code', Status::STATUS_COMPLETED)->value('id');

        DB::transaction(function () use ($demande, $statusId) {
            $demande->update(['status_id' => $statusId]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => Status::STATUS_COMPLETED,
                'commentaire' => 'Dossier clôturé par la Direction.',
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, Status::STATUS_COMPLETED, null));
            }
        } catch (\Throwable $e) {
            Log::error('cloturer: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier clôturé.');
    }

    /**
     * Rejet du dossier par la Direction (décision négative motivée).
     */
    public function rejeter(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);
        $request->validate(['motif' => 'nullable|string|max:2000']);

        abort_if(
            $demande->affectations()->where('statut', 'EN_ATTENTE')->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant de rejeter le dossier.'
        );

        $statusId = Status::where('code', Status::STATUS_REJECTED)->value('id');

        DB::transaction(function () use ($demande, $statusId, $request) {
            $demande->update(['status_id' => $statusId]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => Status::STATUS_REJECTED,
                'commentaire' => 'Dossier rejeté par la Direction.' . ($request->motif ? ' Motif : ' . $request->motif : ''),
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, Status::STATUS_REJECTED, $request->motif));
            }
        } catch (\Throwable $e) {
            Log::error('rejeter: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier rejeté.');
    }

    /**
     * Annulation du dossier par la Direction.
     */
    public function annuler(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);
        $request->validate(['motif' => 'nullable|string|max:2000']);

        abort_if(
            $demande->affectations()->where('statut', 'EN_ATTENTE')->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant d\'annuler le dossier.'
        );

        $statusId = Status::where('code', Status::STATUS_CANCELED)->value('id');

        DB::transaction(function () use ($demande, $statusId, $request) {
            $demande->update(['status_id' => $statusId]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => Status::STATUS_CANCELED,
                'commentaire' => 'Dossier annulé par la Direction.' . ($request->motif ? ' Motif : ' . $request->motif : ''),
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, Status::STATUS_CANCELED, $request->motif));
            }
        } catch (\Throwable $e) {
            Log::error('annuler: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier annulé.');
    }

    /**
     * Affecter un dossier à un ou plusieurs services pour avis simultané.
     */
    public function affecterServices(Request $request, Demande $demande, DemandeWorkflowService $workflowService)
    {
        $request->validate([
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'integer|exists:services,id',
        ]);

        $workflowService->affecterServices($demande, $request->service_ids, auth()->user());

        DemandeActivityLog::create([
            'demande_id' => $demande->id,
            'user_id'    => auth()->id(),
            'action'     => 'affectation_created',
            'metadata'   => ['service_ids' => $request->service_ids],
        ]);

        return redirect()->back()->with('success', 'Dossier affecté à ' . count($request->service_ids) . ' service(s) pour avis.');
    }

    /**
     * Un service soumet son avis sur une affectation.
     */
    public function repondreAffectation(Request $request, Affectation $affectation, DemandeWorkflowService $workflowService)
    {
        $request->validate([
            'avis'   => 'nullable|string|max:3000',
            'statut' => 'required|in:EN_COURS,TERMINE,REJETE',
        ]);

        abort_unless(
            auth()->user()->service_id === $affectation->service_id || auth()->user()->hasRole('admin'),
            403,
            'Seul le service affecté peut soumettre un avis.'
        );

        $workflowService->repondreAffectation($affectation, auth()->user(), $request->avis, $request->statut);

        DemandeActivityLog::create([
            'demande_id' => $affectation->demande_id,
            'user_id'    => auth()->id(),
            'action'     => 'affectation_responded',
            'metadata'   => ['statut' => $request->statut],
        ]);

        return redirect()->back()->with('success', 'Avis enregistré.');
    }
}
