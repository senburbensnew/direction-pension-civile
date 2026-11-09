<?php

namespace App\Notifications;

use App\Enums\RencontreRappelTimingEnum;
use App\Models\Demande;
use App\Models\User;
use App\Notifications\Concerns\UsesStandardChannels;
use App\Queue\ConfiguresQueueRetries;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RappelRencontreProgrammeNotification extends Notification implements ShouldQueue
{
    use ConfiguresQueueRetries;
    use Queueable;
    use UsesStandardChannels;

    public function __construct(
        public readonly Demande $demande,
        public readonly RencontreRappelTimingEnum $timing,
    ) {
        $this->configureQueueRetries();
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return $this->standardVia($notifiable);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $data = $this->demande->data ?? [];
        $slot = $this->slotLabel($data);

        if ($this->forAssignedAgent($notifiable)) {
            return (new MailMessage)
                ->subject('Rappel rendez-vous '.$this->timing->value.' — '.$this->demande->code)
                ->greeting('Bonjour '.$this->recipientName($notifiable).',')
                ->line('Vous avez un rendez-vous Formalités '.$this->timing->labelForAgent().'.')
                ->line('Référence : '.$this->demande->code)
                ->line('Usager : '.$this->usagerLabel($data))
                ->line('Créneau : '.$slot)
                ->line('Mode : '.$this->modeLabel())
                ->action('Voir le rendez-vous', $this->actionUrl())
                ->salutation('Direction de la Pension Civile');
        }

        return (new MailMessage)
            ->subject('Rappel — votre rendez-vous '.$this->timing->labelForUsager())
            ->greeting('Bonjour '.$this->recipientName($notifiable).',')
            ->line('Nous vous rappelons votre rendez-vous avec la Direction de la Pension Civile '.$this->timing->labelForUsager().'.')
            ->line('Référence : '.$this->demande->code)
            ->line('Créneau : '.$slot)
            ->line('Mode : '.$this->modeLabel())
            ->action('Voir mon rendez-vous', $this->actionUrl())
            ->salutation('Direction de la Pension Civile');
    }

    public function toTwilio(object $notifiable): array
    {
        $data = $this->demande->data ?? [];
        $slot = $this->slotLabel($data);

        if ($this->forAssignedAgent($notifiable)) {
            return [
                'message' => 'DPC RDV '.$this->timing->labelForAgent().' : '.$this->demande->code
                    .' — '.$this->usagerLabel($data).' le '.$slot.'.',
            ];
        }

        return [
            'message' => 'DPC : rappel RDV '.$this->timing->labelForUsager()
                .' ('.$this->demande->code.') le '.$slot.'.',
        ];
    }

    public function toArray(object $notifiable): array
    {
        $data = $this->demande->data ?? [];
        $slot = $this->slotLabel($data);

        if ($this->forAssignedAgent($notifiable)) {
            return [
                'demande_id' => $this->demande->id,
                'demande_code' => $this->demande->code,
                'title' => 'Rappel RDV '.$this->timing->value.' — '.$this->demande->code,
                'message' => 'Rendez-vous '.$this->timing->labelForAgent().' avec '
                    .$this->usagerLabel($data).' le '.$slot.'.',
                'icon' => 'calendar',
                'url' => $this->actionUrl(),
                'timing' => $this->timing->value,
            ];
        }

        return [
            'demande_id' => $this->demande->id,
            'demande_code' => $this->demande->code,
            'title' => 'Rappel de rendez-vous',
            'message' => 'Votre rendez-vous '.$this->demande->code.' est prévu '.$this->timing->labelForUsager()
                .' ('.$slot.').',
            'icon' => 'calendar',
            'url' => $this->actionUrl(),
            'timing' => $this->timing->value,
        ];
    }

    private function forAssignedAgent(object $notifiable): bool
    {
        if (! $notifiable instanceof User) {
            return false;
        }

        $agentId = (int) ($this->demande->data['agent_id'] ?? 0);

        return $agentId > 0 && (int) $notifiable->id === $agentId;
    }

    private function recipientName(object $notifiable): string
    {
        if ($notifiable instanceof User) {
            return $notifiable->displayName();
        }

        return 'Bonjour';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function usagerLabel(array $data): string
    {
        return trim(($data['prenom'] ?? '').' '.($data['nom'] ?? '')) ?: 'Pensionné';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function slotLabel(array $data): string
    {
        return trim(($data['date_souhaitee'] ?? '').' à '.($data['heure_souhaitee'] ?? ''));
    }

    private function modeLabel(): string
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
