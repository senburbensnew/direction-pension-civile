<?php

namespace App\Notifications;

use App\Models\Demande;
use App\Models\Service;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeTransferredNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly Demande $demande,
        public readonly Service $toService,
        public readonly ?string $commentaire = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('admin.demandes.show', $this->demande->id);

        $mail = (new MailMessage)
            ->subject('Dossier transféré vers ' . $this->toService->nom . ' — ' . $this->demande->code)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line('Un dossier vous a été transféré et nécessite votre attention.')
            ->line('**Référence :** ' . $this->demande->code)
            ->line('**Type :** ' . $this->demande->title)
            ->line('**Transféré vers :** ' . $this->toService->nom);

        if ($this->commentaire) {
            $mail->line('**Note :** ' . $this->commentaire);
        }

        return $mail
            ->action('Traiter le dossier', $url)
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'demande_id'   => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title'        => $this->demande->title,
            'message'      => 'Dossier ' . $this->demande->code . ' transféré vers ' . $this->toService->nom,
            'commentaire'  => $this->commentaire,
            'url'          => route('admin.demandes.show', $this->demande->id),
            'icon'         => 'arrow-right-circle',
        ];
    }
}
