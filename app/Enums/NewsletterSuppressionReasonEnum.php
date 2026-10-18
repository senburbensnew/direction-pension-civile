<?php

namespace App\Enums;

enum NewsletterSuppressionReasonEnum: string
{
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';
    case Invalid = 'invalid';
    case Complaint = 'complaint';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Unsubscribed => 'Désabonnement',
            self::Bounced => 'Rebond',
            self::Invalid => 'Adresse invalide',
            self::Complaint => 'Plainte',
            self::Manual => 'Suppression manuelle',
        };
    }
}
