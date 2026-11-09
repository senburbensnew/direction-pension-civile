<?php

namespace Tests\Unit\Notifications;

use App\Enums\RencontreRappelTimingEnum;
use App\Enums\TypeDemandeEnum;
use App\Models\Demande;
use App\Models\User;
use App\Notifications\Channels\TwilioSmsChannel;
use App\Notifications\RappelRencontreProgrammeNotification;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class RappelRencontreProgrammeNotificationTest extends TestCase
{
    /** @test */
    public function via_uses_database_mail_and_sms_when_available(): void
    {
        Config::set('notifications.sms_enabled', true);

        $demande = new Demande([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'data' => ['agent_id' => 99],
        ]);

        $notification = new RappelRencontreProgrammeNotification(
            $demande,
            RencontreRappelTimingEnum::ThirtyMinutes,
        );

        $user = User::factory()->make([
            'id' => 42,
            'email' => 'user@example.com',
            'phone' => '+50937000000',
        ]);
        $user->syncOriginal();

        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
        $this->assertContains(config('notifications.sms_channel', TwilioSmsChannel::class), $channels);
    }

    /** @test */
    public function via_omits_sms_when_disabled(): void
    {
        Config::set('notifications.sms_enabled', false);

        $demande = new Demande([
            'type' => TypeDemandeEnum::DEMANDE_RENCONTRE->value,
            'data' => [],
        ]);

        $notification = new RappelRencontreProgrammeNotification(
            $demande,
            RencontreRappelTimingEnum::TwentyFourHours,
        );

        $user = User::factory()->make([
            'email' => 'user@example.com',
            'phone' => '+50937000000',
        ]);

        $channels = $notification->via($user);

        $this->assertContains('database', $channels);
        $this->assertContains('mail', $channels);
        $this->assertNotContains(config('notifications.sms_channel', TwilioSmsChannel::class), $channels);
    }
}
