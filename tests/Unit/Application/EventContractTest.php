<?php

namespace Tests\Unit\Application;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class EventContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function eventProvider(): iterable
    {
        foreach (AppClassDiscovery::eventClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider eventProvider
     */
    public function test_event_class_is_loadable(string $class): void
    {
        $this->assertTrue(class_exists($class));
    }
}
