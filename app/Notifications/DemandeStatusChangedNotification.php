<?php

namespace App\Notifications;

use App\Models\Demande;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Demande $demande,
        public readonly string $newStatusCode,
        public readonly ?string $commentaire = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url   = route('personal.request.show', $this->demande->id);
        $label = $this->statusLabel();

        $mail = (new MailMessage)
            ->subject('Mise à jour de votre demande — ' . $this->demande->code)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Le statut de votre demande a été mis à jour.')
            ->line('**Référence :** ' . $this->demande->code)
            ->line('**Nouveau statut :** ' . $label);

        if ($this->commentaire) {
            $mail->line('**Message de l\'agent :** ' . $this->commentaire);
        }

        return $mail
            ->action('Voir ma demande', $url)
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id'   => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title'        => $this->demande->title,
            'status'       => $this->newStatusCode,
            'message'      => 'Votre demande ' . $this->demande->code . ' : ' . $this->statusLabel(),
            'commentaire'  => $this->commentaire,
            'url'          => route('personal.request.show', $this->demande->id),
            'icon'         => $this->statusIcon(),
        ];
    }

    private function statusLabel(): string
    {
        return match ($this->newStatusCode) {
            'SOUMISE'           => 'Demande reçue',
            'EN_TRAITEMENT'     => 'En cours de traitement',
            'COMPLEMENT_REQUIS' => 'Complément d\'information requis',
            'APPROUVEE'         => 'Approuvée',
            'REJETEE'           => 'Rejetée',
            'TRANSFEREE'        => 'Transférée à un service',
            'CLOTUREE'          => 'Clôturée',
            default             => $this->newStatusCode,
        };
    }

    private function statusIcon(): string
    {
        return match ($this->newStatusCode) {
            'APPROUVEE'         => 'check-circle',
            'REJETEE'           => 'x-circle',
            'COMPLEMENT_REQUIS' => 'alert-circle',
            default             => 'info',
        };
    }
}
