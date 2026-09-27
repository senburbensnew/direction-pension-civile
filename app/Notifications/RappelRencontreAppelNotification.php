<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RappelRencontreAppelNotification extends Notification
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
        $data = $this->demande->data ?? [];
        $service = (string) config('rdv.reminder.service_label', 'Formalités');

        return (new MailMessage)
            ->subject('Rappel d’appel — rendez-vous demain '.$this->demande->code)
            ->greeting('Bonjour '.$notifiable->name.',')
            ->line('Le service des '.$service.' doit rappeler l’usager par appel téléphonique la veille de la rencontre.')
            ->line('Référence : '.$this->demande->code)
            ->line('Usager : '.trim(($data['prenom'] ?? '').' '.($data['nom'] ?? '')))
            ->line('Téléphone : '.($data['telephone'] ?? 'non renseigné'))
            ->line('Date : '.($data['date_souhaitee'] ?? '').' à '.($data['heure_souhaitee'] ?? ''))
            ->action('Voir le rendez-vous', $this->actionUrl())
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        $data = $this->demande->data ?? [];
        $service = (string) config('rdv.reminder.service_label', 'Formalités');

        return [
            'demande_id' => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title' => 'Rappel d’appel — '.$this->demande->code,
            'message' => 'Appel téléphonique du service des '.$service.' à passer aujourd’hui : '
                .trim(($data['prenom'] ?? '').' '.($data['nom'] ?? '')).' au '.($data['telephone'] ?? 'n° non renseigné')
                .' (RDV demain '.($data['heure_souhaitee'] ?? '').').',
            'icon' => 'phone',
            'url' => $this->actionUrl(),
            'channel' => 'appel_telephonique',
        ];
    }

    private function actionUrl(): string
    {
        return route('personal.request.show', $this->demande->id);
    }
}
