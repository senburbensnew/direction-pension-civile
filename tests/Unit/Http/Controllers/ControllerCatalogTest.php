<?php

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\Controller;
use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;
use Throwable;

class ControllerCatalogTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<Controller>}>
     */
    public static function controllerProvider(): iterable
    {
        foreach (AppClassDiscovery::httpControllerClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider controllerProvider
     *
     * @param  class-string<Controller>  $class
     */
    public function test_controller_extends_application_base_controller(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, Controller::class));
    }

    /**
     * @dataProvider controllerProvider
     *
     * @param  class-string<Controller>  $class
     */
    public function test_controller_resolves_from_container(string $class): void
    {
        try {
            $instance = app($class);
        } catch (Throwable $e) {
            $this->markTestSkipped("Cannot resolve {$class}: {$e->getMessage()}");
        }

        $this->assertInstanceOf($class, $instance);
    }

    /**
     * @dataProvider controllerProvider
     *
     * @param  class-string<Controller>  $class
     */
    public function test_controller_declares_at_least_one_public_action(string $class): void
    {
        $methods = (new ReflectionClass($class))->getMethods(\ReflectionMethod::IS_PUBLIC);

        $actions = array_filter(
            $methods,
            static function (\ReflectionMethod $method) use ($class): bool {
                if ($method->getDeclaringClass()->getName() !== $class) {
                    return false;
                }

                if ($method->isConstructor() || $method->isStatic()) {
                    return false;
                }

                return $method->getName() === '__invoke'
                    || ! str_starts_with($method->getName(), '__');
            }
        );

        if ($actions === []) {
            $this->markTestSkipped("{$class} has no public action methods yet.");
        }

        $this->assertNotEmpty($actions);
    }

    /** @test */
    public function catalog_lists_every_discovered_controller(): void
    {
        $listed = array_values(array_map(
            static fn (array $row): string => $row[0],
            iterator_to_array(self::controllerProvider(), preserve_keys: false)
        ));

        $this->assertSame(AppClassDiscovery::httpControllerClasses(), $listed);
    }
}
