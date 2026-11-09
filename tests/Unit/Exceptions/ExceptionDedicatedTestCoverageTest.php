<?php

namespace Tests\Unit\Exceptions;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ExceptionDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_exception_handler_class_has_a_dedicated_test(): void
    {
        $dir = base_path('tests/Unit/Exceptions');

        foreach (AppClassDiscovery::concreteClassesUnder('Exceptions') as $class) {
            $short = class_basename($class);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Exceptions/{$short}Test.php pour {$class}"
            );
        }
    }
}
