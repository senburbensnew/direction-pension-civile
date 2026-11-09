<?php

namespace Tests\Unit\Application;

use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class AppClassesLoadTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function concreteAppClassProvider(): iterable
    {
        foreach (AppClassDiscovery::concreteClassesUnder() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider concreteAppClassProvider
     */
    public function test_concrete_app_class_is_autoloadable(string $class): void
    {
        $this->assertTrue(class_exists($class));

        $reflection = new ReflectionClass($class);

        $this->assertFalse($reflection->isAbstract());
        $this->assertFalse($reflection->isInterface());
        $this->assertFalse($reflection->isTrait());
    }
}
