<?php

namespace Tests\Unit\Policies;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class PolicyDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_policy_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Policies');

        foreach (AppClassDiscovery::policyClasses() as $policyClass) {
            $short = class_basename($policyClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Policies/{$short}Test.php pour {$policyClass}"
            );
        }
    }
}
