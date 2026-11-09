<?php

namespace Tests\Unit\Application;

use Illuminate\Contracts\Queue\ShouldQueue;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class JobContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function jobProvider(): iterable
    {
        foreach (AppClassDiscovery::jobClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider jobProvider
     */
    public function test_job_implements_should_queue(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, ShouldQueue::class));
    }
}
