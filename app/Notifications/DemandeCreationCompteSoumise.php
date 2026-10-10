<?php

namespace App\Notifications;

use App\Models\DemandeCreationCompte;
use App\Notifications\Concerns\UsesStandardChannels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeCreationCompteSoumise extends Notification implements ShouldQueue
{
    use Queueable;
    use UsesStandardChannels;

    public function __construct(
        public DemandeCreationCompte $demande
    ) {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Demande de création de compte soumise')
            ->greeting(
                'Bonjour '.($notifiable->name ?? '')
            )
            ->line(
                'Votre demande de création de compte a bien été soumise.'
            )
            ->line(
                'Référence : '.$this->reference()
            )
            ->line(
                'Votre demande est actuellement en attente de validation.'
            )
            ->line(
                'Vous recevrez une nouvelle notification lorsque votre demande sera traitée.'
            )
            ->salutation('Direction de la Pension Civile');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'demande_creation_compte_soumise',

            'demande_id' => $this->demande->id,

            'reference' => $this->reference(),

            'title' => 'Demande soumise',

            'message' =>
                'Votre demande de création de compte a bien été soumise. '
                .'Elle est actuellement en attente de validation.',

            'created_at' => now()->toDateTimeString(),
        ];
    }

    public function toTwilio(object $notifiable): array
    {
        return [
            'message' =>
                'DPC : votre demande de création de compte a bien été soumise. '
                .'Référence : '.$this->reference().'.',
        ];
    }

    protected function reference(): string
    {
        return $this->demande->reference
            ?? $this->demande->code
            ?? ('DPC-'.str_pad(
                (string) $this->demande->id,
                6,
                '0',
                STR_PAD_LEFT
            ));
    }
}
