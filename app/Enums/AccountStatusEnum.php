<?php

namespace App\Enums;

/**
 * Statut du compte utilisateur (users.account_status), distinct du statut de la demande de création.
 *
 * Correspondance demande → compte :
 * - en_attente  → provisoire
 * - acceptee    → actif
 * - refusee     → bloque
 * - annulee     → desactive (lorsque implémenté)
 */
enum AccountStatusEnum: string
{
    case PROVISOIRE = 'provisoire';
    case ACTIF = 'actif';
    case BLOQUE = 'bloque';
    case DESACTIVE = 'desactive';

    public function label(): string
    {
        return match ($this) {
            self::PROVISOIRE => 'Provisoire',
            self::ACTIF => 'Actif',
            self::BLOQUE => 'Bloqué',
            self::DESACTIVE => 'Désactivé',
        };
    }

    public function isProvisionnel(): bool
    {
        return $this === self::PROVISOIRE;
    }

    public function isActif(): bool
    {
        return $this === self::ACTIF;
    }

    public function allowsLogin(): bool
    {
        return match ($this) {
            self::ACTIF, self::PROVISOIRE => true,
            self::BLOQUE, self::DESACTIVE => false,
        };
    }

    public function isDisabledByAdmin(): bool
    {
        return $this === self::DESACTIVE;
    }

    public static function default(): self
    {
        return self::ACTIF;
    }

    public static function tryFromStored(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if ($value === null || $value === '') {
            return self::ACTIF;
        }

        if (! is_string($value)) {
            return null;
        }

        if ($value === 'en_attente_validation') {
            return self::PROVISOIRE;
        }

        return self::tryFrom($value);
    }
}
