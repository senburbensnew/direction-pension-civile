<?php

namespace App\Notifications;

use App\Models\PensionMandat;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PensionMandatSoumisNotification extends Notification implements ShouldQueue
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
            ->subject('Demande de mandat enregistrée — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Votre demande de mandat a été enregistrée et sera examinée par un agent.')
            ->line('Bénéficiaire : '.$this->mandat->beneficiaire?->displayName())
            ->action('Suivre mes mandats', route('compte.mandats.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pension_mandat_soumis',
            'mandat_id' => $this->mandat->id,
            'title' => 'Demande de mandat enregistrée',
            'message' => 'Votre demande de mandat est en attente de validation.',
            'url' => route('compte.mandats.index'),
            'icon' => 'file-signature',
        ];
    }
}
