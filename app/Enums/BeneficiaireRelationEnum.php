<?php

namespace App\Enums;

enum BeneficiaireRelationEnum: string
{
    case SELF = 'self';
    case PERE = 'pere';
    case MERE = 'mere';
    case TUTEUR = 'tuteur';
    case MANDATAIRE = 'mandataire';

    public function label(): string
    {
        return match ($this) {
            self::SELF => 'Titulaire',
            self::PERE => 'Père',
            self::MERE => 'Mère',
            self::TUTEUR => 'Tuteur subrogé',
            self::MANDATAIRE => 'Mandataire',
        };
    }

    public function isRepresentative(): bool
    {
        return $this !== self::SELF;
    }
}
