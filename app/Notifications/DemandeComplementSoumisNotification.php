<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeComplementSoumisNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Demande $demande)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.demandes.show', $this->demande->id);

        return (new MailMessage)
            ->subject('Complément soumis — ' . $this->demande->code)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('L\'usager a soumis le complément d\'information demandé pour le dossier suivant.')
            ->line('**Référence :** ' . $this->demande->code)
            ->line('**Type :** ' . $this->demande->title)
            ->action('Voir le dossier', $url)
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id'   => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title'        => $this->demande->title,
            'message'      => 'Complément soumis pour le dossier ' . $this->demande->code,
            'url'          => route('admin.demandes.show', $this->demande->id),
            'icon'         => 'check-circle',
        ];
    }
}
