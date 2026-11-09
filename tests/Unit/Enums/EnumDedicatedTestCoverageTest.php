<?php

namespace Tests\Unit\Enums;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class EnumDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_enum_has_a_dedicated_test_class(): void
    {
        $enumsDir = base_path('tests/Unit/Enums');

        foreach (AppClassDiscovery::enumClasses() as $enumClass) {
            $short = class_basename($enumClass);
            $path = $enumsDir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Enums/{$short}Test.php pour {$enumClass}"
            );
        }
    }
}
