<?php

namespace Tests\Unit\Events;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class EventDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_event_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Events');

        foreach (AppClassDiscovery::eventClasses() as $eventClass) {
            $short = class_basename($eventClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Events/{$short}Test.php pour {$eventClass}"
            );
        }
    }
}
