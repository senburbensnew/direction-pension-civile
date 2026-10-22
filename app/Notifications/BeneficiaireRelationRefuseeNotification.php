<?php

namespace App\Notifications;

use App\Models\BeneficiaireRelationDemande;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BeneficiaireRelationRefuseeNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(public readonly BeneficiaireRelationDemande $demande)
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
            ->subject('Demande de relation refusée — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Votre demande de relation pour '.$this->demande->beneficiaire?->displayName().' a été refusée.')
            ->line('Motif : '.$this->demande->refusal_reason)
            ->action('Consulter mes bénéficiaires', route('compte.beneficiaires.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'beneficiaire_relation_refusee',
            'demande_id' => $this->demande->id,
            'title' => 'Demande de relation refusée',
            'message' => $this->demande->refusal_reason ?? 'Demande refusée par un agent.',
            'url' => route('compte.beneficiaires.index'),
            'icon' => 'times-circle',
        ];
    }
}
