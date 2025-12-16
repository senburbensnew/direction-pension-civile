<?php

namespace App\Policies;

use App\Models\Demande;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;


class DemandePolicy
{
use HandlesAuthorization;


public function view(User $user, Demande $demande)
{
if ($user->hasRole('admin') || $user->hasRole('agent')) {
return true;
}


return $demande->user_id === $user->id;
}
}
