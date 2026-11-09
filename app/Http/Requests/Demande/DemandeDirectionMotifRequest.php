<?php

namespace App\Http\Requests\Demande;

class DemandeDirectionMotifRequest extends DemandeMotifRequest
{
    protected function motifMax(): int
    {
        return 2000;
    }
}
