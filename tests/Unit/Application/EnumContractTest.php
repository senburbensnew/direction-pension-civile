<?php

namespace Tests\Unit\Application;

use ReflectionEnum;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;
use UnitEnum;

class EnumContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<UnitEnum>}>
     */
    public static function enumProvider(): iterable
    {
        foreach (AppClassDiscovery::enumClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider enumProvider
     *
     * @param  class-string<UnitEnum>  $class
     */
    public function test_enum_declares_at_least_one_case(string $class): void
    {
        $this->assertNotEmpty($class::cases());
    }

    /**
     * @dataProvider enumProvider
     *
     * @param  class-string<UnitEnum>  $class
     */
    public function test_backed_enum_case_values_are_unique(string $class): void
    {
        $reflection = new ReflectionEnum($class);

        if (! $reflection->isBacked()) {
            $this->assertTrue(true);

            return;
        }

        $values = array_map(
            static fn (UnitEnum $case) => $case->value,
            $class::cases()
        );

        $this->assertSame($values, array_values(array_unique($values)));
    }

    /**
     * @dataProvider enumProvider
     *
     * @param  class-string<UnitEnum>  $class
     */
    public function test_enum_exposes_non_empty_labels_when_label_method_exists(string $class): void
    {
        $case = $class::cases()[0] ?? null;

        if ($case === null || ! method_exists($case, 'label')) {
            $this->assertTrue(true);

            return;
        }

        foreach ($class::cases() as $enumCase) {
            $this->assertNotEmpty($enumCase->label(), "Empty label on {$class}::{$enumCase->name}");
        }
    }
}
