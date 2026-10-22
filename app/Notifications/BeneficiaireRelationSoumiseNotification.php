<?php

namespace App\Notifications;

use App\Models\BeneficiaireRelationDemande;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BeneficiaireRelationSoumiseNotification extends Notification implements ShouldQueue
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
        $beneficiaire = $this->demande->beneficiaire?->displayName() ?? '—';

        return (new MailMessage)
            ->subject('Demande de relation enregistrée — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Votre demande de relation ('.$this->demande->type_relation?->label().') pour '.$beneficiaire.' a été enregistrée.')
            ->line('Un agent des formalités examinera votre justificatif. Vous serez notifié dès qu’une décision sera prise.')
            ->action('Suivre mes bénéficiaires', route('compte.beneficiaires.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'beneficiaire_relation_soumise',
            'demande_id' => $this->demande->id,
            'title' => 'Demande de relation enregistrée',
            'message' => 'En attente de validation par un agent.',
            'url' => route('compte.beneficiaires.index'),
            'icon' => 'user-group',
        ];
    }
}
