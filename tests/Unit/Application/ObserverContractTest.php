<?php

namespace Tests\Unit\Application;

use ReflectionClass;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ObserverContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function observerProvider(): iterable
    {
        foreach (AppClassDiscovery::observerClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider observerProvider
     */
    public function test_observer_exposes_at_least_one_model_hook(string $class): void
    {
        $hooks = ['created', 'updated', 'deleted', 'saved', 'creating', 'updating'];
        $reflection = new ReflectionClass($class);

        $hasHook = false;

        foreach ($hooks as $hook) {
            if ($reflection->hasMethod($hook)) {
                $hasHook = true;
                break;
            }
        }

        $this->assertTrue($hasHook, "{$class} should define an Eloquent observer hook");
    }
}
