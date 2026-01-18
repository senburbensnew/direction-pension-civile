<?php

namespace App\Models;

use App\Models\User;
use App\Models\Status;
use App\Models\Demande;

class DemandeWorkflowService
{
    public function submit(Demande $demande, User $user)
    {
        $demande->update([
            'status_id' => Status::SUBMITTED,
            'current_service_id' => Service::RECEPTION,
        ]);

        $demande->workflows()->create([
            'from_service_id' => null,
            'to_service_id'   => Service::RECEPTION,
            'status_id'       => Status::SUBMITTED,
            'action_by'       => $user->id,
        ]);
    }

    public function transfer(
        Demande $demande,
        Service $toService,
        User $user,
        ?string $commentaire = null
    ) {
        $fromService = $demande->current_service_id;

        $demande->update([
            'current_service_id' => $toService->id,
            'status_id' => Status::TRANSFERRED,
        ]);

        $demande->workflows()->create([
            'from_service_id' => $fromService,
            'to_service_id'   => $toService->id,
            'status_id'       => Status::TRANSFERRED,
            'action_by'       => $user->id,
            'commentaire'     => $commentaire,
        ]);
    }
}