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

        if (
            in_array(
                $demande->currentStep?->code,
                ['REJETEE', 'ANNULEE'],
                true
            )
        ) {
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
        Demande $demande
    ): ?string {
        $token = $demande->visio_token;

        if (!$token) {
            return null;
        }

        $domain = rtrim(
            (string) config(
                'rdv.visio.jitsi_domain',
                'meet.jit.si'
            ),
            '/'
        );

        $room = 'dpc-' . substr(
            $token,
            0,
            32
        );

        return 'https://' . $domain . '/' . $room;
    }
}