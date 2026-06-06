<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DelaiLegalDepasseNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Demande $demande,
        public readonly int $joursDepasse,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("⚠ Délai légal dépassé — Dossier {$this->demande->code}")
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("Le dossier **{$this->demande->code}** a dépassé le délai légal de traitement de **30 jours**.")
            ->line("Délai dépassé de **{$this->joursDepasse} jour(s)**.")
            ->action('Voir le dossier', route('personal.cart'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id'   => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title'        => $this->demande->title,
            'message'      => "Dossier {$this->demande->code} — délai légal dépassé de {$this->joursDepasse} jour(s).",
            'icon'         => 'alert-triangle',
            'url'          => route('personal.cart'),
        ];
    }
}
