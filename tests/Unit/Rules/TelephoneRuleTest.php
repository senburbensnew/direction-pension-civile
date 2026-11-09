<?php

namespace Tests\Unit\Rules;

use App\Rules\Telephone;
use Tests\Support\ValidatesValidationRule;
use Tests\TestCase;

class TelephoneRuleTest extends TestCase
{
    use ValidatesValidationRule;

    /** @test */
    public function normalize_strips_whitespace(): void
    {
        $this->assertSame('+50938123456', Telephone::normalize('+509 38 12 34 56'));
    }

    /** @test */
    public function is_valid_accepts_e164_haiti_example(): void
    {
        $this->assertTrue(Telephone::isValid('+50938123456'));
        $this->assertTrue(Telephone::isValid('+509 38 12 34 56'));
    }

    /** @test */
    public function is_valid_rejects_blank_or_malformed(): void
    {
        $this->assertFalse(Telephone::isValid(''));
        $this->assertFalse(Telephone::isValid('50938123456'));
        $this->assertFalse(Telephone::isValid('+038123456'));
    }

    /** @test */
    public function validate_rejects_non_string(): void
    {
        $this->assertRuleFails(new Telephone, 50938123456);
    }

    /** @test */
    public function validate_accepts_normalized_international_number(): void
    {
        $this->assertRulePasses(new Telephone, '+50938123456');
    }
}
