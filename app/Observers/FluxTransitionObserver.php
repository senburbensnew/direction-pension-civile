<?php

namespace App\Observers;

use App\Models\FluxTransition;
use App\Models\FluxTransitionLog;

class FluxTransitionObserver
{
    public function created(FluxTransition $transition): void
    {
        FluxTransitionLog::create([
            'user_id'             => auth()->id(),
            'action'              => 'created',
            'flux_transition_id'  => $transition->id,
            'before'              => null,
            'after'               => $transition->toArray(),
        ]);
    }

    public function updated(FluxTransition $transition): void
    {
        FluxTransitionLog::create([
            'user_id'             => auth()->id(),
            'action'              => 'updated',
            'flux_transition_id'  => $transition->id,
            'before'              => $transition->getOriginal(),
            'after'               => $transition->toArray(),
        ]);
    }

    public function deleted(FluxTransition $transition): void
    {
        FluxTransitionLog::create([
            'user_id'             => auth()->id(),
            'action'              => 'deleted',
            'flux_transition_id'  => $transition->id,
            'before'              => $transition->toArray(),
            'after'               => null,
        ]);
    }
}
