<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeSubmittedNotification extends Notification
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
            ->subject('Nouvelle demande soumise — ' . $this->demande->code)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Une nouvelle demande a été soumise et attend votre traitement.')
            ->line('**Référence :** ' . $this->demande->code)
            ->line('**Type :** ' . $this->demande->title)
            ->line('**Soumise le :** ' . $this->demande->submitted_at?->format('d/m/Y à H:i'))
            ->action('Voir la demande', $url)
            ->line('Merci de traiter ce dossier dans les meilleurs délais.')
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id'   => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title'        => $this->demande->title,
            'message'      => 'Nouvelle demande soumise : ' . $this->demande->code,
            'url'          => route('admin.demandes.show', $this->demande->id),
            'icon'         => 'file-plus',
        ];
    }
}
