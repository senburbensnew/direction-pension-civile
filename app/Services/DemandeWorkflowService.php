<?php

namespace App\Services;

use App\Models\Demande;
use App\Models\Service;
use App\Models\Status;
use App\Models\User;

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
            'action_by_user_id' => $user->id,
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
            'action_by_user_id' => $user->id,
            'commentaire'     => $commentaire,
        ]);
    }
}