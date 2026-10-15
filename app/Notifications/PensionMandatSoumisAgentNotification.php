<?php

namespace App\Notifications;

use App\Models\PensionMandat;
use App\Notifications\Concerns\UsesStandardChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PensionMandatSoumisAgentNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use UsesStandardChannels;

    public function __construct(public readonly PensionMandat $mandat)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $viaCompte = $this->mandat->isFromDemandeCreationCompte();

        return (new MailMessage)
            ->subject('Nouvelle demande de mandat à valider — DPC')
            ->greeting('Bonjour '.($notifiable->displayName() ?? '').',')
            ->line($viaCompte
                ? 'Un mandataire a ouvert un dossier via demande de création de compte ; le mandat reste à approuver après acceptation du compte.'
                : 'Une demande de mandat est en attente de validation.')
            ->line('Mandataire : '.$this->mandat->mandataire?->displayName())
            ->line('Bénéficiaire : '.$this->mandat->beneficiaire?->displayName())
            ->action('Traiter les mandats', route('formalites.pension-mandats.index'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'pension_mandat_soumis_agent',
            'mandat_id' => $this->mandat->id,
            'title' => 'Demande de mandat à valider',
            'message' => 'Mandataire : '.$this->mandat->mandataire?->displayName(),
            'url' => route('formalites.pension-mandats.index'),
            'icon' => 'file-signature',
        ];
    }
}
