<?php

namespace App\Enums;

enum RencontreRappelTimingEnum: string
{
    case TwentyFourHours = '24h';
    case ThirtyMinutes = '30m';

    public function leadMinutes(): int
    {
        return match ($this) {
            self::TwentyFourHours => 24 * 60,
            self::ThirtyMinutes => 30,
        };
    }

    public function sentAtDataKey(): string
    {
        return match ($this) {
            self::TwentyFourHours => 'rappel_24h_envoye_at',
            self::ThirtyMinutes => 'rappel_30m_envoye_at',
        };
    }

    public function labelForUsager(): string
    {
        return match ($this) {
            self::TwentyFourHours => 'demain',
            self::ThirtyMinutes => 'dans 30 minutes',
        };
    }

    public function labelForAgent(): string
    {
        return match ($this) {
            self::TwentyFourHours => 'dans 24 heures',
            self::ThirtyMinutes => 'dans 30 minutes',
        };
    }
}
