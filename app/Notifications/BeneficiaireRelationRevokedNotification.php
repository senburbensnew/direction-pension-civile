<?php

namespace App\Notifications;

use App\Models\UserBeneficiaireLink;
use App\Notifications\Concerns\UsesStandardChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BeneficiaireRelationRevokedNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use UsesStandardChannels;

    public function __construct(public readonly UserBeneficiaireLink $link)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Relation révoquée — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line('Votre accès en tant que '.$this->link->relation?->label().' pour '.$this->link->beneficiaire?->displayName().' a été révoqué.')
            ->action('Mes bénéficiaires', route('compte.beneficiaires.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'beneficiaire_relation_revoked',
            'link_id' => $this->link->id,
            'title' => 'Relation révoquée',
            'message' => 'Accès retiré pour '.$this->link->beneficiaire?->displayName().'.',
            'url' => route('compte.beneficiaires.index'),
            'icon' => 'ban',
        ];
    }
}
