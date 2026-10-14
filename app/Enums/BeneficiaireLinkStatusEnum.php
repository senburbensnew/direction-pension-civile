<?php

namespace App\Enums;

enum BeneficiaireLinkStatusEnum: string
{
    case EN_ATTENTE = 'en_attente';
    case ACTIVE = 'active';
    case REFUSE = 'refuse';
    case REVOQUE = 'revoke';
    case EXPIRE = 'expire';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::ACTIVE => 'Active',
            self::REFUSE => 'Refusée',
            self::REVOQUE => 'Révoquée',
            self::EXPIRE => 'Expirée',
        };
    }

    public function grantsAccess(): bool
    {
        return $this === self::ACTIVE;
    }
}
