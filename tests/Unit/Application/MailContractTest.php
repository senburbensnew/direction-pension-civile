<?php

namespace Tests\Unit\Application;

use Illuminate\Mail\Mailable;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class MailContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function mailableProvider(): iterable
    {
        foreach (AppClassDiscovery::mailableClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider mailableProvider
     */
    public function test_mailable_extends_base_mailable(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, Mailable::class));
    }
}
