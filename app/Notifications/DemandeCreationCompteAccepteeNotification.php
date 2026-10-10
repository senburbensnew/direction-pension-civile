<?php

namespace App\Notifications;

use App\Models\DemandeCreationCompte;
use App\Notifications\Concerns\UsesStandardChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeCreationCompteAccepteeNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use UsesStandardChannels;

    public function __construct(public readonly DemandeCreationCompte $demande)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Compte activé — Direction de la Pension Civile')
            ->greeting('Bonjour '.($notifiable->name ?? '').',')
            ->line('Votre demande de création de compte a été acceptée.')
            ->line('Référence : '.$this->reference())
            ->line('Votre compte est maintenant actif. Vous pouvez vous connecter avec votre identifiant et votre mot de passe.')
            ->action('Se connecter', route('login'))
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'demande_creation_compte_acceptee',
            'demande_id' => $this->demande->id,
            'reference' => $this->reference(),
            'title' => 'Compte activé',
            'message' => 'Votre demande de création de compte ('.$this->reference().') a été acceptée. Votre compte est actif.',
            'url' => route('login'),
            'icon' => 'check-circle',
        ];
    }

    public function toTwilio(object $notifiable): array
    {
        return [
            'message' => 'DPC : votre compte est activé. Réf. '.$this->reference().'. Connectez-vous sur le portail.',
        ];
    }

    protected function reference(): string
    {
        return $this->demande->reference
            ?? $this->demande->code
            ?? ('DPC-'.str_pad((string) $this->demande->id, 6, '0', STR_PAD_LEFT));
    }
}
