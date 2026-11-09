<?php

namespace Tests\Unit\Listeners;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ListenerDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_listener_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Listeners');

        foreach (AppClassDiscovery::listenerClasses() as $listenerClass) {
            $short = class_basename($listenerClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Listeners/{$short}Test.php pour {$listenerClass}"
            );
        }
    }
}
