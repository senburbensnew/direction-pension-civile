<?php

namespace App\Services;

use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class RencontreVisioService
{
    public function attachTo(Demande $demande): Demande
    {
        if (($demande->data['modalite'] ?? '') !== 'visio') {
            return $demande;
        }

        if (filled($demande->visio_token)) {
            return $demande;
        }

        $token = Str::lower(
            bin2hex(random_bytes(32))
        );

        $demande->update([
            'visio_token' => $token,
            'data' => array_merge(
                $demande->data ?? [],
                [
                    'visio_token' => $token,
                    'visio_url' => route(
                        'demandes.rencontre.visio',
                        $token
                    ),
                ]
            ),
        ]);

        return $demande->fresh();
    }

    public function findByToken(string $token): ?Demande
    {
        if ($token === '') {
            return null;
        }

        return Demande::query()
            ->where('visio_token', $token)
            ->where(
                'type',
                TypeDemandeEnum::DEMANDE_RENCONTRE->value
            )
            ->first();
    }

    public function canAccess(
        ?User $user,
        Demande $demande
    ): bool {
        if (
            !$user
            || !$demande->isRencontre()
            || ($demande->data['modalite'] ?? '') !== 'visio'
        ) {
            return false;
        }

        // ✅ Plus robuste : s'appuie sur rencontreStatut() qui
        // retombe sur data['rdv_statut'] puis sur le workflow.
        if ($demande->rencontreStatut()->isTerminal()) {
            return false;
        }

        if (
            (int) $demande->created_by
            === (int) $user->id
        ) {
            return true;
        }

        if (
            (int) ($demande->data['agent_id'] ?? 0)
            === (int) $user->id
        ) {
            return true;
        }

        return $demande
            ->assignments()
            ->whereNull('ended_at')
            ->where('user_id', $user->id)
            ->exists();
    }

    public function startsAt(
        Demande $demande
    ): ?Carbon {
        $date =
            $demande->data['date_souhaitee']
            ?? null;

        $time = Demande::normalizeRencontreTime(
            $demande->data['heure_souhaitee']
            ?? null
        );

        if (!$date || !$time) {
            return null;
        }

        try {
            return Carbon::parse(
                $date . ' ' . $time
            );
        } catch (\Throwable) {
            return null;
        }
    }

    public function opensAt(
        Demande $demande
    ): ?Carbon {
        $start = $this->startsAt($demande);

        return $start?->copy()->subMinutes(
            (int) config(
                'rdv.visio.activate_minutes_before',
                15
            )
        );
    }

    public function closesAt(
        Demande $demande
    ): ?Carbon {
        $start = $this->startsAt($demande);

        if (!$start) {
            return null;
        }

        $slot = (int) config(
            'rdv.slot_minutes',
            15
        );

        $after = (int) config(
            'rdv.visio.keep_open_minutes_after',
            15
        );

        return $start
            ->copy()
            ->addMinutes($slot + $after);
    }

    public function isActive(
        Demande $demande,
        ?Carbon $now = null
    ): bool {
        $now ??= now();

        $opens = $this->opensAt($demande);
        $closes = $this->closesAt($demande);

        return $opens !== null
            && $closes !== null
            && $now->betweenIncluded(
                $opens,
                $closes
            );
    }

    public function status(
        Demande $demande,
        ?Carbon $now = null
    ): string {
        $now ??= now();

        $opens = $this->opensAt($demande);
        $closes = $this->closesAt($demande);

        if (!$opens || !$closes) {
            return 'indisponible';
        }

        if ($now->lt($opens)) {
            return 'en_attente';
        }

        if ($now->gt($closes)) {
            return 'termine';
        }

        return 'actif';
    }

    public function embedUrl(
        Demande $demande,
        ?User $user = null
    ): ?string {
        $token = $demande->visio_token;

        if (!$token) {
            return null;
        }

        $domain = rtrim(
            (string) config(
                'rdv.visio.domain',
                'kmeet.infomaniak.com'
            ),
            '/'
        );

        $prefix = (string) config(
            'rdv.visio.room_prefix',
            'dpc'
        );

        $length = (int) config(
            'rdv.visio.room_hash_length',
            32
        );

        $room = $prefix . '-' . substr($token, 0, $length);

        $params = [
            'config.prejoinPageEnabled' =>
                config('rdv.visio.prejoin') ? 'true' : 'false',

            'config.startWithAudioMuted' =>
                config('rdv.visio.start_audio_muted') ? 'true' : 'false',

            'config.startWithVideoMuted' =>
                config('rdv.visio.start_video_muted') ? 'true' : 'false',

            'config.disableDeepLinking' => 'true',
            'config.disableProfile'     => 'true',

            'interfaceConfig.SHOW_JITSI_WATERMARK'    => 'false',
            'interfaceConfig.SHOW_BRANDING_WATERMARK' => 'false',
        ];

        if (config('rdv.visio.prefill_user_info') && $user) {
            $displayName =
                $user->displayName()
                ?? $user->name
                ?? null;

            if ($displayName) {
                $params['userInfo.displayName'] =
                    '"' . addslashes($displayName) . '"';
            }

            if (! empty($user->email)) {
                $params['userInfo.email'] =
                    '"' . addslashes($user->email) . '"';
            }
        }

        return 'https://' . $domain . '/' . $room
            . '#' . http_build_query($params, '', '&');
    }
}