<?php

namespace App\Enums;

enum DemandeStatusEnum: string
{
    case BROUILLON = 'BROUILLON';
    case SOUMISE = 'SOUMISE';
    case COMPLEMENT_REQUIS = 'COMPLEMENT_REQUIS';
}