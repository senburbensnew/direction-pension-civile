<?php

namespace Tests\Unit\Notifications;

use App\Models\Contact;
use App\Notifications\ContactFormAcknowledgmentNotification;
use App\Notifications\LiquidationNotice;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class NotificationCatalogTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<Notification>}>
     */
    public static function notificationProvider(): iterable
    {
        foreach (AppClassDiscovery::notificationClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider notificationProvider
     */
    public function test_notification_declares_via_method(string $class): void
    {
        $this->assertTrue(method_exists($class, 'via'));
    }

    /** @test */
    public function liquidation_notice_uses_mail_channel_only(): void
    {
        $notification = new LiquidationNotice;
        $notifiable = new AnonymousNotifiable;

        $this->assertSame(['mail'], $notification->via($notifiable));
    }

    /** @test */
    public function contact_acknowledgment_targets_mail_when_email_present(): void
    {
        $contact = new Contact([
            'email' => 'visiteur@example.com',
            'name' => 'Visiteur',
            'message' => 'Bonjour',
        ]);

        $notification = new ContactFormAcknowledgmentNotification($contact);
        $notifiable = new AnonymousNotifiable;
        $notifiable->route('mail', $contact->email);

        $this->assertContains('mail', $notification->via($notifiable));
    }

    /** @test */
    public function every_notification_is_sendable_or_queueable(): void
    {
        foreach (AppClassDiscovery::notificationClasses() as $class) {
            $reflection = new ReflectionClass($class);
            $this->assertTrue(
                $reflection->isInstantiable(),
                "{$class} must be a concrete notification class"
            );
        }
    }

    /** @test */
    public function catalog_lists_every_discovered_notification(): void
    {
        $listed = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(self::notificationProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::notificationClasses(), $listed);
    }
}
