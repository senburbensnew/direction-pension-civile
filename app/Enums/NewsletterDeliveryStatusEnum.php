<?php

namespace App\Enums;

enum NewsletterDeliveryStatusEnum: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Bounced = 'bounced';
    case Suppressed = 'suppressed';

    public function label(): string
    {
        return match ($this) {
            self::Queued => 'En file',
            self::Sent => 'Envoyé',
            self::Delivered => 'Délivré',
            self::Failed => 'Échec',
            self::Bounced => 'Rebond',
            self::Suppressed => 'Supprimé (liste)',
        };
    }
}
