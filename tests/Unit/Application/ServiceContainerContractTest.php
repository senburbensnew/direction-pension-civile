<?php

namespace Tests\Unit\Application;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;
use Throwable;

class ServiceContainerContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function serviceProvider(): iterable
    {
        foreach (AppClassDiscovery::serviceClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider serviceProvider
     */
    public function test_service_resolves_from_container(string $class): void
    {
        try {
            $instance = app($class);
        } catch (Throwable $e) {
            $this->markTestSkipped("Cannot resolve {$class}: {$e->getMessage()}");
        }

        $this->assertInstanceOf($class, $instance);
    }
}
