<?php

namespace App\Enums;

enum PensionMandatTypeEnum: string
{
    case PROCURATION_CONSULAIRE = 'procuration_consulaire';
    case PROCURATION = 'procuration';
    case MANDAT_SPECIAL = 'mandat_special';
    case TUTELLE = 'tutelle';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::PROCURATION_CONSULAIRE => 'Procuration consulaire',
            self::PROCURATION => 'Procuration',
            self::MANDAT_SPECIAL => 'Mandat spécial',
            self::TUTELLE => 'Tutelle / curatelle',
            self::AUTRE => 'Autre (à préciser)',
        };
    }

    public function requiresPrecision(): bool
    {
        return $this === self::AUTRE;
    }

    public static function selectOptions(): array
    {
        return self::cases();
    }

    public static function storedType(string $mandatType, ?string $autrePrecision = null): string
    {
        $enum = self::tryFrom($mandatType) ?? self::AUTRE;

        if ($enum === self::AUTRE) {
            $precision = trim((string) $autrePrecision);

            return $precision !== '' ? 'autre:'.$precision : self::AUTRE->value;
        }

        return $enum->value;
    }

    public static function displayLabel(string $storedType): string
    {
        if (str_starts_with($storedType, 'autre:')) {
            return 'Autre — '.substr($storedType, 6);
        }

        return self::tryFrom($storedType)?->label()
            ?? str_replace('_', ' ', $storedType);
    }
}
