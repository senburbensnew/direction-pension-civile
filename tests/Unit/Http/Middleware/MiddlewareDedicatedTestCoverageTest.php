<?php

namespace Tests\Unit\Middleware;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class MiddlewareDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_middleware_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Http/Middleware');

        foreach (AppClassDiscovery::middlewareClasses() as $middlewareClass) {
            $short = class_basename($middlewareClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Http/Middleware/{$short}Test.php pour {$middlewareClass}"
            );
        }
    }
}
