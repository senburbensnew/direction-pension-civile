<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DossierInactifNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Demande $demande,
        public readonly int $joursInactif,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Relance — Dossier inactif depuis {$this->joursInactif} jours : {$this->demande->code}")
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Le dossier **{$this->demande->code}** est en attente de traitement dans votre service depuis **{$this->joursInactif} jours**.")
            ->line('Type : ' . $this->demande->title)
            ->action('Voir le dossier', route('personal.cart'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id'   => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title'        => $this->demande->title,
            'message'      => "Dossier {$this->demande->code} inactif depuis {$this->joursInactif} jours — relance automatique.",
            'icon'         => 'alert-circle',
            'url'          => route('personal.cart'),
        ];
    }
}
