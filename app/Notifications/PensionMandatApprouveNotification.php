<?php

namespace App\Notifications;

use App\Models\PensionMandat;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PensionMandatApprouveNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(public readonly PensionMandat $mandat)
    {
        $this->configureQueueRetries();

        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Mandat approuvé — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Votre mandat a été approuvé. Vous pouvez agir pour le bénéficiaire dans le périmètre défini.')
            ->line('Bénéficiaire : '.$this->mandat->beneficiaire?->displayName())
            ->action('Ouvrir le portail', route('personal.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pension_mandat_approuve',
            'mandat_id' => $this->mandat->id,
            'title' => 'Mandat approuvé',
            'message' => 'Votre mandat est actif.',
            'url' => route('personal.index'),
            'icon' => 'check-circle',
        ];
    }
}
