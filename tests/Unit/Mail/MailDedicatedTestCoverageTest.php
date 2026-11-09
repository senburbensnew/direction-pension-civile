<?php

namespace Tests\Unit\Mail;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class MailDedicatedTestCoverageTest extends TestCase
{
    /** @test */
    public function every_app_mailable_has_a_dedicated_test_class(): void
    {
        $dir = base_path('tests/Unit/Mail');

        foreach (AppClassDiscovery::mailableClasses() as $mailableClass) {
            $short = class_basename($mailableClass);
            $path = $dir.DIRECTORY_SEPARATOR.$short.'Test.php';

            $this->assertFileExists(
                $path,
                "Ajoute tests/Unit/Mail/{$short}Test.php pour {$mailableClass}"
            );
        }
    }
}
