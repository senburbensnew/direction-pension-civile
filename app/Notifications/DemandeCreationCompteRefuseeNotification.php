<?php

namespace App\Notifications;

use App\Models\DemandeCreationCompte;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeCreationCompteRefuseeNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(
        public readonly DemandeCreationCompte $demande,
        public readonly string $reason,
    ) {
        $this->configureQueueRetries();

        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable, database: false);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Demande de compte refusée — Direction de la Pension Civile')
            ->greeting('Bonjour,')
            ->line('Votre demande de création de compte n’a pas pu être acceptée.')
            ->line('Référence : '.$this->reference())
            ->line('Motif : '.$this->reason)
            ->line('Vous pouvez soumettre une nouvelle demande si vous le souhaitez.')
            ->action('Nouvelle demande', route('demandes.compte.create'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toTwilio(object $notifiable): array
    {
        return [
            'message' => 'DPC : demande de compte refusée. Réf. '.$this->reference().'.',
        ];
    }

    protected function reference(): string
    {
        return $this->demande->reference
            ?? $this->demande->code
            ?? ('DPC-'.str_pad((string) $this->demande->id, 6, '0', STR_PAD_LEFT));
    }
}
