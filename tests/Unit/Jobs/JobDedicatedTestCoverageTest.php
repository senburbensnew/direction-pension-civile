<?php

namespace Tests\Unit\Jobs;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class JobDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_job_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Jobs');

        foreach (AppClassDiscovery::jobClasses() as $jobClass) {
            $short = class_basename($jobClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Jobs/{$short}Test.php pour {$jobClass}"
            );
        }
    }
}
