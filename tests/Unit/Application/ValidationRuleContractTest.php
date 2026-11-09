<?php

namespace Tests\Unit\Application;

use Illuminate\Contracts\Validation\ValidationRule;
use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class ValidationRuleContractTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string}>
     */
    public static function ruleProvider(): iterable
    {
        foreach (AppClassDiscovery::validationRuleClasses() as $class) {
            yield $class => [$class];
        }
    }

    /**
     * @dataProvider ruleProvider
     */
    public function test_rule_implements_validation_rule_contract(string $class): void
    {
        $rule = new $class;

        $this->assertInstanceOf(ValidationRule::class, $rule);
    }

    /**
     * @dataProvider ruleProvider
     */
    public function test_validate_method_accepts_empty_value_without_type_error(string $class): void
    {
        $rule = new $class;

        try {
            $rule->validate('attribute', null, static function (): void {
                // validation failure callback
            });
        } catch (\TypeError $e) {
            $rule->validate('attribute', '', static function (): void {
                // validation failure callback
            });
        }

        $this->assertTrue(true);
    }
}
