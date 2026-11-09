<?php

namespace Tests\Unit\Application;

use App\Http\Controllers\Controller;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class HttpControllerContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function controllerProvider(): iterable
    {
        foreach (AppClassDiscovery::httpControllerClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider controllerProvider
     */
    public function test_controller_extends_base_controller(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, Controller::class));
    }
}
