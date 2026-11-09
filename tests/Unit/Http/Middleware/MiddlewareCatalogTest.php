<?php

namespace Tests\Unit\Middleware;

use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class MiddlewareCatalogTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function middlewareProvider(): iterable
    {
        foreach (AppClassDiscovery::middlewareClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider middlewareProvider
     */
    public function test_middleware_is_instantiable(string $class): void
    {
        $reflection = new ReflectionClass($class);

        $this->assertTrue($reflection->isInstantiable());
        $this->assertTrue($reflection->hasMethod('handle'));
    }

    /** @test */
    public function catalog_lists_every_discovered_middleware(): void
    {
        $listed = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(self::middlewareProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::middlewareClasses(), $listed);
    }
}
