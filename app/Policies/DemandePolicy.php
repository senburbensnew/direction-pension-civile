<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DemandePolicy
{
    use HandlesAuthorization;

    public function access(User $user, Demande $demande)
    {
        return $user->service_id === $demande->current_service_id;
    }

    public function view(User $user, Demande $demande)
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $demande->user_id === $user->id;
    }
}
