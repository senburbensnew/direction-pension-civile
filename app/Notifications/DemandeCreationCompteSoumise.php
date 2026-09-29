<?php

namespace App\Notifications;

use App\Models\DemandeCreationCompte;
// use App\Notifications\Channels\TwilioSmsChannel; // À réactiver plus tard
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DemandeCreationCompteSoumise extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DemandeCreationCompte $demande
    ) {
        $this->afterCommit();
    }

    /**
     * Canaux de notification actuellement utilisés.
     */
    public function via(object $notifiable): array
    {
        return [
            'database',
            'mail',

            // Twilio à activer plus tard :
            // TwilioSmsChannel::class,
        ];
    }

    /**
     * Notification par email.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Demande de création de compte soumise')
            ->greeting(
                'Bonjour ' . ($notifiable->name ?? '')
            )
            ->line(
                'Votre demande de création de compte a bien été soumise.'
            )
            ->line(
                'Référence : ' . $this->reference()
            )
            ->line(
                'Votre demande est actuellement en attente de validation.'
            )
            ->line(
                'Vous recevrez une nouvelle notification lorsque votre demande sera traitée.'
            )
            ->salutation('Cordialement,')
            ->salutation('Direction de la Pension Civile');
    }

    /**
     * Notification in-app enregistrée dans la table notifications.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'demande_creation_compte_soumise',

            'demande_id' => $this->demande->id,

            'reference' => $this->reference(),

            'title' => 'Demande soumise',

            'message' =>
                'Votre demande de création de compte a bien été soumise. '
                . 'Elle est actuellement en attente de validation.',

            'created_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Notification SMS Twilio.
     *
     * À réactiver plus tard lorsque le SMS sera nécessaire.
     */
    /*
    public function toTwilio(object $notifiable): array
    {
        return [
            'to' => $notifiable->phone,
            'message' =>
                'DPC : votre demande de création de compte a bien été soumise. '
                . 'Référence : ' . $this->reference() . '.',
        ];
    }
    */

    /**
     * Référence de la demande.
     */
    protected function reference(): string
    {
        return $this->demande->reference
            ?? $this->demande->code
            ?? ('DPC-' . str_pad(
                (string) $this->demande->id,
                6,
                '0',
                STR_PAD_LEFT
            ));
    }
}