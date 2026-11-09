<?php

namespace Tests\Unit\Application;

use Illuminate\Notifications\Notification;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class NotificationContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
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
    public function test_notification_extends_base_notification(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, Notification::class));
    }
}
