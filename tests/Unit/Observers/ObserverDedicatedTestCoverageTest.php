<?php

namespace Tests\Unit\Observers;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ObserverDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_observer_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Observers');

        foreach (AppClassDiscovery::observerClasses() as $observerClass) {
            $short = class_basename($observerClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Observers/{$short}Test.php pour {$observerClass}"
            );
        }
    }
}
