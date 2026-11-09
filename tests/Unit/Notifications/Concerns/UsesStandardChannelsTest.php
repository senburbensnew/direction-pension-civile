<?php

namespace Tests\Unit\Notifications\Concerns;

use App\Models\User;
use App\Notifications\Concerns\UsesStandardChannels;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Tests\TestCase;

class UsesStandardChannelsTest extends TestCase
{
    /** @test */
    public function standard_via_includes_mail_when_notifiable_has_email(): void
    {
        $notification = new StandardChannelsProbeNotification(database: false);
        $user = User::factory()->make(['email' => 'user@example.com']);

        $this->assertContains('mail', $notification->exposeVia($user));
    }

    /** @test */
    public function standard_via_includes_database_for_eloquent_notifiable(): void
    {
        $notification = new StandardChannelsProbeNotification(database: true);
        $user = User::factory()->make(['email' => 'user@example.com']);

        $this->assertContains('database', $notification->exposeVia($user));
        $this->assertContains('mail', $notification->exposeVia($user));
    }

    /** @test */
    public function anonymous_notifiable_uses_routed_mail_address(): void
    {
        $notification = new StandardChannelsProbeNotification(database: false);

        /** @var AnonymousNotifiable $notifiable */
        $notifiable = NotificationFacade::route('mail', 'routed@example.com');

        $this->assertSame(['mail'], $notification->exposeVia($notifiable));
    }
}

final class StandardChannelsProbeNotification extends Notification
{
    use UsesStandardChannels;

    public function __construct(private bool $database = false) {}

    /**
     * @return list<string|class-string>
     */
    public function exposeVia(object $notifiable): array
    {
        return $this->standardVia($notifiable, database: $this->database);
    }
}
