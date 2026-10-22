<?php

namespace App\Notifications;

use App\Models\BeneficiaireRelationDemande;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BeneficiaireRelationSoumiseAgentNotification extends Notification implements ShouldQueue
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
            ->subject('Nouvelle demande de relation à valider — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Une demande de relation représentant est en attente.')
            ->line('Demandeur : '.$this->demande->user?->displayName())
            ->line('Bénéficiaire : '.$this->demande->beneficiaire?->displayName())
            ->line('Relation : '.$this->demande->type_relation?->label())
            ->action('Traiter la demande', route('formalites.beneficiaire-relations.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'beneficiaire_relation_soumise_agent',
            'demande_id' => $this->demande->id,
            'title' => 'Demande de relation à valider',
            'message' => ($this->demande->user?->displayName() ?? 'Usager').' — '.$this->demande->type_relation?->label(),
            'url' => route('formalites.beneficiaire-relations.index'),
            'icon' => 'clipboard-check',
        ];
    }
}
