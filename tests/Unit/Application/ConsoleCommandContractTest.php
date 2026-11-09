<?php

namespace Tests\Unit\Application;

use Illuminate\Console\Command;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ConsoleCommandContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function commandProvider(): iterable
    {
        foreach (AppClassDiscovery::consoleCommandClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider commandProvider
     */
    public function test_console_command_extends_artisan_command(string $class): void
    {
        $this->assertTrue(is_subclass_of($class, Command::class));
    }

    /**
     * @dataProvider commandProvider
     */
    public function test_console_command_has_signature_or_name(string $class): void
    {
        $command = app($class);

        $this->assertTrue(
            property_exists($command, 'signature') || property_exists($command, 'name'),
            "{$class} should define \$signature or \$name"
        );
    }
}
