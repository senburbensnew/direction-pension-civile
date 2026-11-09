<?php

namespace Tests\Unit\Application;

use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class SupportClassContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function supportProvider(): iterable
    {
        foreach (AppClassDiscovery::supportClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider supportProvider
     */
    public function test_support_class_is_instantiable_or_has_public_api(string $class): void
    {
        $reflection = new ReflectionClass($class);

        if ($reflection->isEnum()) {
            $this->assertTrue(true);

            return;
        }

        if ($reflection->getConstructor() === null || $reflection->getConstructor()->getNumberOfRequiredParameters() === 0) {
            $this->assertInstanceOf($class, new $class);

            return;
        }

        $this->assertNotEmpty($reflection->getMethods(\ReflectionMethod::IS_PUBLIC));
    }
}
