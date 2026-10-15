<?php

namespace App\Enums;

enum PensionMandatStatusEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case ACTIF = 'actif';
    case REFUSE = 'refuse';
    case REVOQUE = 'revoque';
    case EXPIRE = 'expire';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente de validation',
            self::ACTIF => 'Actif',
            self::REFUSE => 'Refusé',
            self::REVOQUE => 'Révoqué',
            self::EXPIRE => 'Expiré',
        };
    }

    public function allowsAccess(): bool
    {
        return $this === self::ACTIF;
    }

    public function isPending(): bool
    {
        return $this === self::EN_ATTENTE;
    }
}
