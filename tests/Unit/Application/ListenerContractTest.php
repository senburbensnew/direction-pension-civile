<?php

namespace Tests\Unit\Application;

use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ListenerContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function listenerProvider(): iterable
    {
        foreach (AppClassDiscovery::listenerClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider listenerProvider
     */
    public function test_listener_exposes_handle_method(string $class): void
    {
        $reflection = new ReflectionClass($class);

        $this->assertTrue($reflection->hasMethod('handle'));
    }
}
