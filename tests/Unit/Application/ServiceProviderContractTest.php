<?php

namespace Tests\Unit\Application;

use Illuminate\Support\ServiceProvider;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ServiceProviderContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function providerProvider(): iterable
    {
        foreach (AppClassDiscovery::serviceProviderClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider providerProvider
     */
    public function test_service_provider_can_be_constructed(string $class): void
    {
        $provider = new $class($this->app);

        $this->assertInstanceOf(ServiceProvider::class, $provider);
    }
}
