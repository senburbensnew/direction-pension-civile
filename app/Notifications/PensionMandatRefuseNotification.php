<?php

namespace App\Notifications;

use App\Models\PensionMandat;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PensionMandatRefuseNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(
        public readonly PensionMandat $mandat,
        public readonly string $reason,
    ) {
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
            ->subject('Mandat refusé — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Votre demande de mandat a été refusée.')
            ->line('Motif : '.$this->reason)
            ->action('Consulter mes mandats', route('compte.mandats.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pension_mandat_refuse',
            'mandat_id' => $this->mandat->id,
            'title' => 'Mandat refusé',
            'message' => 'Motif : '.$this->reason,
            'url' => route('compte.mandats.index'),
            'icon' => 'times-circle',
        ];
    }
}
