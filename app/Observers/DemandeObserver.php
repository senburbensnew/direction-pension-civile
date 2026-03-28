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
        if (! $demande->wasChanged('status_id')) {
            return;
        }

        $demande->loadMissing('status', 'user');

        $newCode = $demande->status->code;
        $owner   = $demande->user;

        // Notify direction users when a demande is first submitted
        if ($newCode === 'SOUMISE') {
            $this->notifyDirectionUsers($demande);
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
     * Notify all users assigned to the Direction service.
     */
    private function notifyDirectionUsers(Demande $demande): void
    {
        $directionUsers = User::whereHas('service', fn ($q) => $q->where('code', Service::DIRECTION))
            ->orWhereHas('roles', fn ($q) => $q->where('name', 'direction'))
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
