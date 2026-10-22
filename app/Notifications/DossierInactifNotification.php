<?php

namespace App\Notifications;

use App\Models\Demande;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DossierInactifNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(
        public readonly Demande $demande,
        public readonly int $joursInactif,
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
            ->subject("Relance — Dossier inactif depuis {$this->joursInactif} jours : {$this->demande->code}")
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line("Le dossier **{$this->demande->code}** est en attente de traitement dans votre service depuis **{$this->joursInactif} jours**.")
            ->line('Type : '.$this->demande->title)
            ->action('Voir le dossier', route('personal.cart'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id' => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title' => $this->demande->title,
            'message' => "Dossier {$this->demande->code} inactif depuis {$this->joursInactif} jours — relance automatique.",
            'icon' => 'alert-circle',
            'url' => route('personal.cart'),
        ];
    }
}
