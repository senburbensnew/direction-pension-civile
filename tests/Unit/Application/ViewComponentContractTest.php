<?php

namespace Tests\Unit\Application;

use Illuminate\View\Component;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ViewComponentContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function componentProvider(): iterable
    {
        foreach (AppClassDiscovery::viewComponentClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider componentProvider
     */
    public function test_view_component_extends_laravel_component(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, Component::class));
    }
}
