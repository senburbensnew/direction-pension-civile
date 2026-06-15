<?php

namespace App\Http\Controllers;

use App\Models\AgentDelegation;
use App\Models\Demande;
use App\Models\DemandeHistory;
use App\Models\DemandeInteraction;
use App\Models\DemandeMessage;
use App\Models\RequiredCircuitService;
use App\Models\Service;
use App\Models\WorkflowStep;
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
        activity('demande')->performedOn($demande)->causedBy(auth()->user())->log('viewed');

        $requiredConditions = $this->buildRequiredConditions($demande);

        return view('demandes.admin.edit', compact('demande', 'requiredConditions'));
    }

    private function buildRequiredConditions(Demande $demande): array
    {
        $required = RequiredCircuitService::where(function ($q) use ($demande) {
            $q->where('type_demande', $demande->type)->orWhereNull('type_demande');
        })->with('service')->get();

        if ($required->isEmpty()) return [];

        $visitedServiceIds = $demande->interactions()
            ->where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->where('statut', DemandeInteraction::STATUT_ACCEPTE)
            ->whereNotNull('to_service_id')
            ->pluck('to_service_id')
            ->unique();

        return $required->map(fn ($req) => [
            'service_name' => $req->service->nom,
            'is_met'       => $visitedServiceIds->contains($req->service_id),
        ])->all();
    }

    public function updateStatus(Request $request, Demande $demande)
    {
        $request->validate([
            'etat'        => 'required|string',
            'commentaire' => 'nullable|string',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $stepId = WorkflowStep::idForCode($request->etat);
            if ($stepId) $demande->update(['current_step_id' => $stepId]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'STATUS_UPDATED',
                'statut'      => $request->etat,
                'commentaire' => $request->commentaire,
                'changed_by'  => auth()->id(),
            ]);
        });

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

    public function annotate(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403, 'Seule la Direction peut annoter un dossier.');
        abort_if($demande->isClosed(), 403, 'Ce dossier est clôturé et ne peut plus être modifié.');

        abort_if(
            $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_AVIS)
                ->where('to_service_id', auth()->user()->service_id)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->exists(),
            403,
            'Votre service est en mode consultation pour ce dossier. Soumettez votre avis avant toute autre action.'
        );

        $actingServiceIds = AgentDelegation::actingServiceIds(auth()->id(), auth()->user()->service_id ?? 0);
        abort_if(
            $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_TRANSFERT)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->whereIn('to_service_id', $actingServiceIds)
                ->exists(),
            403,
            'Confirmez d\'abord la réception de ce dossier avant de l\'annoter.'
        );

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
                'event'       => 'ANNOTATED',
                'statut'      => $demande->currentStep?->code,
                'commentaire' => 'Annotation par la Direction : ' . $request->annotation,
                'changed_by'  => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Dossier annoté avec succès.');
    }

    public function requestComplement(Request $request, Demande $demande)
    {
        abort_if($demande->isClosed(), 403, 'Ce dossier est clôturé et ne peut plus être modifié.');

        abort_if(
            $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_AVIS)
                ->where('to_service_id', auth()->user()->service_id)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->exists(),
            403,
            'Votre service est en mode consultation pour ce dossier. Soumettez votre avis avant toute autre action.'
        );

        $actingServiceIds = AgentDelegation::actingServiceIds(auth()->id(), auth()->user()->service_id ?? 0);
        abort_if(
            $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_TRANSFERT)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->whereIn('to_service_id', $actingServiceIds)
                ->exists(),
            403,
            'Confirmez d\'abord la réception de ce dossier avant de demander un complément.'
        );

        $request->validate([
            'message' => 'required|string|max:3000',
        ]);

        DB::transaction(function () use ($demande, $request) {
            $complementStepId = WorkflowStep::idForCode('COMPLEMENT_REQUIS');
            if ($complementStepId) $demande->update(['current_step_id' => $complementStepId]);

            DemandeMessage::create([
                'demande_id' => $demande->id,
                'sender_id'  => auth()->id(),
                'body'       => $request->message,
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'COMPLEMENT_REQUESTED',
                'statut'      => 'COMPLEMENT_REQUIS',
                'commentaire' => 'Complément requis : ' . $request->message,
                'changed_by'  => auth()->id(),
            ]);
        });

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

    public function transfererDemande(TransfertDemandeRequest $request, DemandeWorkflowService $workflowService)
    {
        $demande = Demande::findOrFail($request->demande_id);

        abort_if($demande->isClosed(), 403, 'Ce dossier est clôturé et ne peut plus être transféré.');

        abort_if(
            !$demande->isAnnotated(),
            403,
            'Le dossier doit être annoté par la Direction avant d\'être transféré.'
        );

        abort_if(
            $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_AVIS)
                ->where('to_service_id', auth()->user()->service_id)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->exists(),
            403,
            'Votre service est en mode consultation pour ce dossier. Soumettez votre avis avant toute autre action.'
        );

        abort_if(
            $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_AVIS)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->exists(),
            403,
            'Des avis sont encore en attente. Tous les services consultés doivent soumettre leur avis avant tout transfert.'
        );

        $actingServiceIds = AgentDelegation::actingServiceIds(auth()->id(), auth()->user()->service_id ?? 0);
        abort_if(
            $demande->interactions()
                ->where('type', DemandeInteraction::TYPE_TRANSFERT)
                ->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)
                ->whereIn('to_service_id', $actingServiceIds)
                ->exists(),
            403,
            'Confirmez d\'abord la réception de ce dossier avant de le transférer.'
        );

        $toService = Service::findOrFail($request->service_id);

        $workflowService->transfer($demande, $toService, auth()->user(), $request->commentaire);

        activity('demande')->performedOn($demande)->causedBy(auth()->user())
            ->withProperties(['to_service' => $toService->nom, 'commentaire' => $request->commentaire])
            ->log('transferred');

        try {
            foreach ($toService->users as $user) {
                $user->notify(new DemandeTransferredNotification($demande, $toService, $request->commentaire));
            }
        } catch (\Throwable $e) {
            Log::error('transfererDemande: service notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('personal.cart')->with('success', 'Transfert initié vers ' . $toService->nom . '. En attente de confirmation de réception.');
    }

    public function accepterReception(Request $request, DemandeInteraction $interaction, DemandeWorkflowService $workflowService)
    {
        $user = auth()->user();
        $actingServiceIds = $user->service_id
            ? AgentDelegation::actingServiceIds($user->id, $user->service_id)
            : [];

        abort_unless(
            in_array($interaction->to_service_id, $actingServiceIds) || $user->hasRole('admin'),
            403,
            'Seul le service destinataire (ou son délégué) peut confirmer la réception.'
        );

        $workflowService->accepterReception($interaction, $user);

        activity('demande')->performedOn($interaction->demande)->causedBy(auth()->user())->log('reception_accepted');

        return redirect()->back()->with('success', 'Réception confirmée. Le dossier est maintenant en cours de traitement.');
    }

    public function refuserReception(Request $request, DemandeInteraction $interaction, DemandeWorkflowService $workflowService)
    {
        $request->validate(['motif' => 'nullable|string|max:1000']);

        $user = auth()->user();
        $actingServiceIds = $user->service_id
            ? AgentDelegation::actingServiceIds($user->id, $user->service_id)
            : [];

        abort_unless(
            in_array($interaction->to_service_id, $actingServiceIds) || $user->hasRole('admin'),
            403,
            'Seul le service destinataire (ou son délégué) peut refuser la réception.'
        );

        $workflowService->refuserReception($interaction, $user, $request->motif);

        activity('demande')->performedOn($interaction->demande)->causedBy(auth()->user())
            ->withProperties(['motif' => $request->motif])
            ->log('reception_refused');

        return redirect()->back()->with('success', 'Transfert refusé. Le dossier a été retourné au service expéditeur.');
    }

    private function hasBeenProcessedByAService(Demande $demande): bool
    {
        return $demande->interactions()
            ->where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->where('statut', DemandeInteraction::STATUT_ACCEPTE)
            ->whereNotNull('from_service_id')
            ->exists();
    }

    private function missingRequiredServices(Demande $demande): array
    {
        $requiredServiceIds = RequiredCircuitService::where(function ($q) use ($demande) {
            $q->where('type_demande', $demande->type)->orWhereNull('type_demande');
        })->pluck('service_id')->unique();

        if ($requiredServiceIds->isEmpty()) return [];

        $visitedServiceIds = $demande->interactions()
            ->where('type', DemandeInteraction::TYPE_TRANSFERT)
            ->where('statut', DemandeInteraction::STATUT_ACCEPTE)
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

        abort_if(
            $demande->created_by === auth()->id(),
            403,
            'Conflit d\'intérêt : vous êtes le créateur de ce dossier et ne pouvez pas l\'approuver.'
        );

        abort_unless(
            $this->hasBeenProcessedByAService($demande),
            403,
            'Ce dossier doit avoir été traité et acheminé par un autre service avant d\'être approuvé par la Direction.'
        );

        abort_if(
            $demande->interactions()->where('type', DemandeInteraction::TYPE_AVIS)->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant d\'approuver le dossier.'
        );

        $missing = $this->missingRequiredServices($demande);
        abort_if(
            count($missing) > 0,
            403,
            'Le circuit de traitement n\'est pas complet. Services manquants : ' . implode(', ', $missing) . '.'
        );

        $stepId = WorkflowStep::idForCode('APPROUVEE');

        DB::transaction(function () use ($demande, $stepId) {
            if ($stepId) $demande->update(['current_step_id' => $stepId]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'APPROVED',
                'statut'      => 'APPROUVEE',
                'commentaire' => 'Dossier approuvé par la Direction.',
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, 'APPROUVEE', null));
            }
        } catch (\Throwable $e) {
            Log::error('approuver: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier approuvé.');
    }

    public function cloturer(Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);

        abort_if(
            $demande->created_by === auth()->id(),
            403,
            'Conflit d\'intérêt : vous êtes le créateur de ce dossier et ne pouvez pas le clôturer.'
        );

        abort_unless(
            $this->hasBeenProcessedByAService($demande),
            403,
            'Ce dossier doit avoir été traité et acheminé par un autre service avant d\'être clôturé par la Direction.'
        );

        abort_if(
            $demande->interactions()->where('type', DemandeInteraction::TYPE_AVIS)->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant de clôturer le dossier.'
        );

        $missing = $this->missingRequiredServices($demande);
        abort_if(
            count($missing) > 0,
            403,
            'Le circuit de traitement n\'est pas complet. Services manquants : ' . implode(', ', $missing) . '.'
        );

        $stepId = WorkflowStep::idForCode('FINALISEE');

        DB::transaction(function () use ($demande, $stepId) {
            if ($stepId) $demande->update(['current_step_id' => $stepId]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'FINALIZED',
                'statut'      => 'FINALISEE',
                'commentaire' => 'Dossier clôturé par la Direction.',
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, 'FINALISEE', null));
            }
        } catch (\Throwable $e) {
            Log::error('cloturer: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier clôturé.');
    }

    public function rejeter(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);
        $request->validate(['motif' => 'nullable|string|max:2000']);

        abort_if(
            $demande->interactions()->where('type', DemandeInteraction::TYPE_AVIS)->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant de rejeter le dossier.'
        );

        $stepId = WorkflowStep::idForCode('REJETEE');

        DB::transaction(function () use ($demande, $stepId, $request) {
            if ($stepId) $demande->update(['current_step_id' => $stepId]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'REJECTED',
                'statut'      => 'REJETEE',
                'commentaire' => 'Dossier rejeté par la Direction.' . ($request->motif ? ' Motif : ' . $request->motif : ''),
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, 'REJETEE', $request->motif));
            }
        } catch (\Throwable $e) {
            Log::error('rejeter: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier rejeté.');
    }

    public function annuler(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);
        $request->validate(['motif' => 'nullable|string|max:2000']);

        abort_if(
            $demande->interactions()->where('type', DemandeInteraction::TYPE_AVIS)->where('statut', DemandeInteraction::STATUT_EN_ATTENTE)->exists(),
            403,
            'Des avis sont encore en attente. Veuillez les traiter avant d\'annuler le dossier.'
        );

        $stepId = WorkflowStep::idForCode('ANNULEE');

        DB::transaction(function () use ($demande, $stepId, $request) {
            if ($stepId) $demande->update(['current_step_id' => $stepId]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'CANCELED',
                'statut'      => 'ANNULEE',
                'commentaire' => 'Dossier annulé par la Direction.' . ($request->motif ? ' Motif : ' . $request->motif : ''),
                'changed_by'  => auth()->id(),
            ]);
        });

        try {
            $demande->load('user');
            if ($demande->user) {
                $demande->user->notify(new DemandeStatusChangedNotification($demande, 'ANNULEE', $request->motif));
            }
        } catch (\Throwable $e) {
            Log::error('annuler: notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Dossier annulé.');
    }

    public function rouvrir(Request $request, Demande $demande)
    {
        abort_unless(auth()->user()->hasRole('direction'), 403);
        abort_unless($demande->isClosed(), 422, 'Ce dossier n\'est pas clôturé.');

        $request->validate(['motif' => 'nullable|string|max:2000']);

        $soumiseStepId = WorkflowStep::idForCode('SOUMISE');

        DB::transaction(function () use ($demande, $soumiseStepId, $request) {
            if ($soumiseStepId) $demande->update(['current_step_id' => $soumiseStepId]);
            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'event'       => 'REOPENED',
                'statut'      => 'SOUMISE',
                'commentaire' => 'Dossier réouvert par la Direction.' . ($request->motif ? ' Motif : ' . $request->motif : ''),
                'changed_by'  => auth()->id(),
            ]);
        });

        return redirect()->back()->with('success', 'Dossier réouvert avec succès.');
    }

    public function affecterServices(Request $request, Demande $demande, DemandeWorkflowService $workflowService)
    {
        abort_if($demande->isClosed(), 403, 'Ce dossier est clôturé et ne peut plus être modifié.');

        $request->validate([
            'service_ids'   => 'required|array|min:1',
            'service_ids.*' => 'integer|exists:services,id',
        ]);

        $workflowService->affecterServices($demande, $request->service_ids, auth()->user());

        activity('demande')->performedOn($demande)->causedBy(auth()->user())
            ->withProperties(['service_ids' => $request->service_ids])
            ->log('affectation_created');

        return redirect()->back()->with('success', 'Dossier affecté à ' . count($request->service_ids) . ' service(s) pour avis.');
    }

    public function repondreAffectation(Request $request, DemandeInteraction $interaction, DemandeWorkflowService $workflowService)
    {
        $request->validate([
            'avis'   => 'nullable|string|max:3000',
            'statut' => 'required|in:EN_COURS,TERMINE,REJETE',
        ]);

        abort_unless(
            auth()->user()->service_id === $interaction->to_service_id,
            403,
            'Seul le service affecté peut soumettre un avis.'
        );

        $workflowService->repondreAffectation($interaction, auth()->user(), $request->avis, $request->statut);

        activity('demande')->performedOn($interaction->demande)->causedBy(auth()->user())
            ->withProperties(['statut' => $request->statut])
            ->log('affectation_responded');

        if (in_array($request->statut, ['TERMINE', 'REJETE'])) {
            return redirect()->route('personal.cart')
                ->with('success', 'Avis soumis. Vous n\'avez plus accès à ce dossier.');
        }

        return redirect()->back()->with('success', 'Avis mis à jour.');
    }

    public function assignerAgent(Request $request, Demande $demande, DemandeWorkflowService $workflowService)
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'note'    => ['nullable', 'string', 'max:500'],
        ]);

        $agent = \App\Models\User::findOrFail($request->user_id);

        abort_if(
            $demande->current_service_id && $agent->service_id !== $demande->current_service_id,
            422,
            "L'agent ne fait pas partie du service courant du dossier."
        );

        $workflowService->assignerAgent($demande, $agent, auth()->user(), $request->note);

        activity('demande')->performedOn($demande)->causedBy(auth()->user())
            ->withProperties(['agent' => $agent->name])
            ->log('agent_assigned');

        return redirect()->back()->with('success', "Dossier affecté à {$agent->name}.");
    }
}
