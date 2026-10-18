<?php

namespace App\Enums;

enum NewsletterCampaignStatusEnum: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Sending = 'sending';
    case Sent = 'sent';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Brouillon',
            self::Scheduled => 'Planifiée',
            self::Sending => 'Envoi en cours',
            self::Sent => 'Envoyée',
            self::Cancelled => 'Annulée',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Scheduled], true);
    }
}
