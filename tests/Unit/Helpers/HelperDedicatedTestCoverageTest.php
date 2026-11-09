<?php

namespace Tests\Unit\Helpers;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class HelperDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_helper_class_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Helpers');

        foreach (AppClassDiscovery::helperClasses() as $helperClass) {
            $short = class_basename($helperClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Helpers/{$short}Test.php pour {$helperClass}"
            );
        }
    }
}
