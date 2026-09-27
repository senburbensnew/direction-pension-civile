<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RencontreAgentAssigneNotification extends Notification
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

        return (new MailMessage)
            ->subject('Nouveau rendez-vous Formalités — '.$this->demande->code)
            ->greeting('Bonjour '.$notifiable->displayName().',')
            ->line('Un pensionné a pris rendez-vous avec vous.')
            ->line('**Référence :** '.$this->demande->code)
            ->line('**Usager :** '.$this->usager())
            ->line('**Date :** '.($data['date_souhaitee'] ?? '—').' à '.($data['heure_souhaitee'] ?? '—'))
            ->line('**Mode :** '.$this->mode())
            ->action('Voir le rendez-vous', $this->actionUrl())
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        $data = $this->demande->data ?? [];

        return [
            'demande_id' => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title' => 'Nouveau rendez-vous — '.$this->demande->code,
            'message' => 'Un rendez-vous vous a été attribué : '.$this->usager()
                .' le '.($data['date_souhaitee'] ?? '').' à '.($data['heure_souhaitee'] ?? '')
                .' ('.$this->demande->code.').',
            'url' => $this->actionUrl(),
            'icon' => 'calendar',
        ];
    }

    private function usager(): string
    {
        $data = $this->demande->data ?? [];

        return trim(($data['prenom'] ?? '').' '.($data['nom'] ?? '')) ?: 'Pensionné';
    }

    private function mode(): string
    {
        return (($this->demande->data['modalite'] ?? '') === 'physique')
            ? 'Présentiel'
            : 'Visioconférence';
    }

    private function actionUrl(): string
    {
        return route('personal.request.show', $this->demande->id);
    }
}
