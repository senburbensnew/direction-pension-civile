<?php

namespace Tests\Unit\Rules;

use App\Rules\CodePension;
use Tests\Support\ValidatesValidationRule;
use Tests\TestCase;

class CodePensionRuleTest extends TestCase
{
    use ValidatesValidationRule;

    /** @test */
    public function is_valid_accepts_seven_and_eight_series_formats(): void
    {
        $this->assertTrue(CodePension::isValid('7-AB12C'));
        $this->assertTrue(CodePension::isValid('8-34321'));
        $this->assertFalse(CodePension::isValid('9-12345'));
        $this->assertFalse(CodePension::isValid(''));
    }

    /** @test */
    public function validate_skips_blank_values(): void
    {
        $this->assertRulePasses(new CodePension, null);
        $this->assertRulePasses(new CodePension, '');
    }

    /** @test */
    public function validate_rejects_invalid_code_when_present(): void
    {
        $this->assertRuleFails(new CodePension, 'INVALID');
        $this->assertRulePasses(new CodePension, '8-34321');
    }
}
