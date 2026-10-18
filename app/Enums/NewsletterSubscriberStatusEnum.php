<?php

namespace App\Enums;

enum NewsletterSubscriberStatusEnum: string
{
    case Pending = 'pending';
    case Subscribed = 'subscribed';
    case Unsubscribed = 'unsubscribed';
    case Bounced = 'bounced';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'En attente de confirmation',
            self::Subscribed => 'Abonné',
            self::Unsubscribed => 'Désabonné',
            self::Bounced => 'Rebond (bounce)',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-800',
            self::Subscribed => 'bg-green-100 text-green-800',
            self::Unsubscribed => 'bg-gray-100 text-gray-700',
            self::Bounced => 'bg-red-100 text-red-800',
        };
    }
}
