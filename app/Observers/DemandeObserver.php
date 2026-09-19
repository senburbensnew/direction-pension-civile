<?php

namespace App\Observers;

use App\Models\Demande;
use App\Models\Service;
use App\Models\User;
use App\Notifications\DemandeStatusChangedNotification;
use App\Notifications\DemandeSubmittedNotification;
use Illuminate\Support\Facades\Log;

class DemandeObserver
{
    /**
     * Fires after a Demande is saved. Sends notifications on status changes.
     */
    public function updated(Demande $demande): void
    {
        if (! $demande->wasChanged('current_step_id')) {
            return;
        }

        $demande->load('currentStep', 'user');

        $newCode = $demande->currentStep?->code;
        $owner   = $demande->user;

        // Soumission initiale : notifier la Direction + confirmer à l'usager.
        // Skip Direction si retour après COMPLEMENT_REQUIS (déjà géré ailleurs).
        if ($newCode === 'SOUMISE') {
            $previousStepId = $demande->getOriginal('current_step_id');
            $previousCode = $previousStepId
                ? \App\Models\WorkflowStep::find($previousStepId)?->code
                : null;

            if ($previousCode !== 'COMPLEMENT_REQUIS') {
                $this->notifyDirectionUsers($demande);
                $this->notifyOwnerOnSubmission($demande, $owner);
            }
            return;
        }

        // Notify the demande owner for any other status change (not draft).
        // COMPLEMENT_REQUIS is handled directly in the controller with the message attached.
        if ($owner && !in_array($newCode, ['BROUILLON', 'COMPLEMENT_REQUIS'])) {
            try {
                $owner->notify(new DemandeStatusChangedNotification($demande, $newCode));
            } catch (\Throwable $e) {
                Log::error('DemandeObserver: could not notify owner', [
                    'demande_id' => $demande->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Confirme à l'usager que sa demande a bien été soumise / reçue.
     */
    private function notifyOwnerOnSubmission(Demande $demande, ?User $owner): void
    {
        if (!$owner) {
            return;
        }

        try {
            $owner->notify(new DemandeStatusChangedNotification(
                $demande,
                'SOUMISE',
                'Votre demande a bien été soumise et reçue par la Direction.'
            ));
        } catch (\Throwable $e) {
            Log::error('DemandeObserver: could not notify owner on submission', [
                'user_id'    => $owner->id,
                'demande_id' => $demande->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify all users assigned to the Direction service.
     */
    private function notifyDirectionUsers(Demande $demande): void
    {
        $directionUsers = User::whereHas('service', fn ($q) => $q->where('code', Service::DIRECTION))
            ->orWhereHas('roles', fn ($q) => $q->whereIn('name', User::DIRECTION_ROLES))
            ->get();

        foreach ($directionUsers as $user) {
            try {
                $user->notify(new DemandeSubmittedNotification($demande));
            } catch (\Throwable $e) {
                Log::error('DemandeObserver: could not notify direction user', [
                    'user_id'    => $user->id,
                    'demande_id' => $demande->id,
                    'error'      => $e->getMessage(),
                ]);
            }
        }
    }
}
