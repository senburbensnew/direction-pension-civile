<?php

namespace Tests\Unit\Notifications;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class NotificationDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function notification_catalog_test_covers_all_notification_classes(): void
    {
        $this->assertFileExists(base_path('tests/Unit/Notifications/NotificationCatalogTest.php'));

        $catalog = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(NotificationCatalogTest::notificationProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::notificationClasses(), $catalog);
    }

    /** @test */
    public function every_notification_channel_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Notifications/Channels');

        foreach (AppClassDiscovery::notificationChannelClasses() as $channelClass) {
            $short = class_basename($channelClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Notifications/Channels/{$short}Test.php pour {$channelClass}"
            );
        }
    }
}
