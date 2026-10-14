<?php

namespace App\Enums;

enum BeneficiaireRelationDemandeStatusEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case ACCEPTEE = 'acceptee';
    case REFUSEE = 'refusee';
    case REVOQUEE = 'revokee';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente de validation',
            self::ACCEPTEE => 'Acceptée',
            self::REFUSEE => 'Refusée',
            self::REVOQUEE => 'Révoquée',
        };
    }

    public function isPending(): bool
    {
        return $this === self::EN_ATTENTE;
    }
}
