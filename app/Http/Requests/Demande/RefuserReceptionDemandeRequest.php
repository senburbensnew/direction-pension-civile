<?php

namespace App\Http\Requests\Demande;

class RefuserReceptionDemandeRequest extends DemandeMotifRequest
{
    protected function motifMax(): int
    {
        return 1000;
    }
}
