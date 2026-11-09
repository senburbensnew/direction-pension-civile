<?php

namespace Tests\Unit\Application;

use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class HelperClassContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function helperProvider(): iterable
    {
        foreach (AppClassDiscovery::helperClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider helperProvider
     */
    public function test_helper_class_exposes_public_methods(string $class): void
    {
        $reflection = new ReflectionClass($class);

        $this->assertNotEmpty($reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
    }
}
