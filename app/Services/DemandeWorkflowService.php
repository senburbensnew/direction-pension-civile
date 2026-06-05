<?php

namespace App\Services;

use App\Models\Affectation;
use App\Models\Demande;
use App\Models\DemandeHistory;
use App\Models\DemandeWorkflow;
use App\Models\FluxTransition;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DemandeWorkflowService
{
    public function submit(Demande $demande, User $user)
    {
        $directionId = Service::where('code', Service::DIRECTION)->value('id');

        $demande->update([
            'status_id'          => Status::where('code', 'SOUMISE')->value('id'),
            'current_service_id' => $directionId,
        ]);

        $demande->workflows()->create([
            'from_service_id'   => null,
            'to_service_id'     => $directionId,
            'status_id'         => $demande->status_id,
            'action_by_user_id' => $user->id,
            'reception_status'  => 'accepted',
        ]);
    }

    public function validateTransition(int $fromServiceId, int $toServiceId, ?string $typeDemandeCode = null): bool
    {
        return FluxTransition::allowed($fromServiceId, $toServiceId, $typeDemandeCode);
    }

    /**
     * Create parallel affectations for a dossier to multiple services simultaneously.
     *
     * @param  int[]  $serviceIds
     */
    public function affecterServices(Demande $demande, array $serviceIds, User $user): void
    {
        DB::transaction(function () use ($demande, $serviceIds, $user) {
            foreach ($serviceIds as $serviceId) {
                Affectation::updateOrCreate(
                    ['demande_id' => $demande->id, 'service_id' => $serviceId],
                    [
                        'statut'               => 'EN_ATTENTE',
                        'avis'                 => null,
                        'affecte_par_user_id'  => $user->id,
                        'date_affectation'     => now(),
                        'date_reponse'         => null,
                    ]
                );
            }

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => $demande->status->code,
                'commentaire' => 'Dossier affecté à ' . count($serviceIds) . ' service(s) pour avis.',
                'changed_by'  => $user->id,
            ]);
        });
    }

    public function repondreAffectation(Affectation $affectation, User $user, ?string $avis, string $statut): void
    {
        $affectation->update([
            'statut'        => $statut,
            'avis'          => $avis,
            'date_reponse'  => now(),
        ]);

        DemandeHistory::create([
            'demande_id'  => $affectation->demande_id,
            'statut'      => $affectation->demande->status->code,
            'commentaire' => 'Avis du service ' . $affectation->service->nom . ' : ' . $statut . ($avis ? ' — ' . $avis : ''),
            'changed_by'  => $user->id,
        ]);
    }

    /**
     * Initiate a transfer — the dossier moves to the destination service
     * with status TRANSFERT_EN_ATTENTE. The destination must accept or refuse.
     */
    public function transfer(
        Demande $demande,
        Service $toService,
        User $user,
        ?string $commentaire = null
    ): DemandeWorkflow {
        $fromServiceId = $demande->current_service_id;

        abort_unless(
            $this->validateTransition($fromServiceId, $toService->id, $demande->type),
            403,
            'Transfert non autorisé selon le circuit défini.'
        );

        $statusId = Status::where('code', Status::STATUS_TRANSFERT_EN_ATTENTE)->value('id');

        DB::transaction(function () use ($demande, $toService, $user, $commentaire, $fromServiceId, $statusId) {
            $demande->update([
                'current_service_id' => $toService->id,
                'status_id'          => $statusId,
            ]);

            $this->workflow = $demande->workflows()->create([
                'from_service_id'   => $fromServiceId,
                'to_service_id'     => $toService->id,
                'status_id'         => $statusId,
                'action_by_user_id' => $user->id,
                'commentaire'       => $commentaire,
                'reception_status'  => 'pending',
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => Status::STATUS_TRANSFERT_EN_ATTENTE,
                'commentaire' => 'Transfert initié vers : ' . $toService->nom . ($commentaire ? ' — ' . $commentaire : ''),
                'changed_by'  => $user->id,
            ]);
        });

        return $this->workflow;
    }

    /**
     * The destination service accepts the transfer.
     * Status moves to EN_COURS (or stays at whatever it was before the transfer).
     */
    public function accepterReception(DemandeWorkflow $workflow, User $user): void
    {
        abort_unless($workflow->isPending(), 422, 'Ce transfert a déjà été traité.');

        $demande = $workflow->demande;
        $statusId = Status::where('code', Status::STATUS_IN_PROGRESS)->value('id');

        DB::transaction(function () use ($workflow, $demande, $user, $statusId) {
            $workflow->update([
                'reception_status'     => 'accepted',
                'reception_at'         => now(),
                'reception_by_user_id' => $user->id,
            ]);

            $demande->update(['status_id' => $statusId]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => Status::STATUS_IN_PROGRESS,
                'commentaire' => 'Réception confirmée par : ' . $workflow->toService->nom,
                'changed_by'  => $user->id,
            ]);
        });
    }

    /**
     * The destination service refuses the transfer.
     * The dossier is returned to the originating service.
     */
    public function refuserReception(DemandeWorkflow $workflow, User $user, ?string $motif = null): void
    {
        abort_unless($workflow->isPending(), 422, 'Ce transfert a déjà été traité.');

        $demande    = $workflow->demande;
        $statusId   = Status::where('code', Status::STATUS_TRANSFERT_REFUSE)->value('id');
        $fromService = $workflow->from_service_id;

        DB::transaction(function () use ($workflow, $demande, $user, $motif, $statusId, $fromService) {
            $workflow->update([
                'reception_status'     => 'refused',
                'reception_motif'      => $motif,
                'reception_at'         => now(),
                'reception_by_user_id' => $user->id,
            ]);

            // Return dossier to the originating service
            $demande->update([
                'current_service_id' => $fromService,
                'status_id'          => $statusId,
            ]);

            DemandeHistory::create([
                'demande_id'  => $demande->id,
                'statut'      => Status::STATUS_TRANSFERT_REFUSE,
                'commentaire' => 'Transfert refusé par : ' . $workflow->toService->nom . ($motif ? ' — ' . $motif : ''),
                'changed_by'  => $user->id,
            ]);
        });
    }

    /** @var DemandeWorkflow */
    private DemandeWorkflow $workflow;
}
