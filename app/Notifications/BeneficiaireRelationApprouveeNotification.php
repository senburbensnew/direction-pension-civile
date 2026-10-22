<?php

namespace App\Notifications;

use App\Models\BeneficiaireRelationDemande;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BeneficiaireRelationApprouveeNotification extends Notification implements ShouldQueue
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
        $beneficiaire = $this->demande->beneficiaire;

        return (new MailMessage)
            ->subject('Relation approuvée — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Votre demande de relation ('.$this->demande->type_relation?->label().') a été acceptée.')
            ->line('Bénéficiaire : '.($beneficiaire?->displayName() ?? '—'))
            ->line('Vous pouvez maintenant agir pour ce dossier dans les limites des permissions accordées.')
            ->action('Utiliser ce contexte', $beneficiaire
                ? route('compte.beneficiaires.context', $beneficiaire)
                : route('compte.beneficiaires.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        $beneficiaire = $this->demande->beneficiaire;

        return [
            'type' => 'beneficiaire_relation_approuvee',
            'demande_id' => $this->demande->id,
            'title' => 'Relation approuvée',
            'message' => 'Accès actif pour '.($beneficiaire?->displayName() ?? 'le bénéficiaire').'.',
            'url' => $beneficiaire
                ? route('compte.beneficiaires.context', $beneficiaire)
                : route('compte.beneficiaires.index'),
            'icon' => 'check-circle',
        ];
    }
}
