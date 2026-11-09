<?php

namespace Tests\Unit\Support;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class SupportDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_support_class_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Support');

        foreach (AppClassDiscovery::supportClasses() as $supportClass) {
            $short = class_basename($supportClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Support/{$short}Test.php pour {$supportClass}"
            );
        }
    }
}
