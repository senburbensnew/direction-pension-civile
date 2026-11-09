<?php

namespace Tests\Unit\Rules;

use App\Rules\Cin;
use App\Rules\Nif;
use App\Rules\NifNinu;
use App\Rules\Ninu;
use Tests\Support\ValidatesValidationRule;
use Tests\TestCase;

class IdentificationFormatRulesTest extends TestCase
{
    use ValidatesValidationRule;

    private const VALID_DASHED = '123-456-789-0';

    /** @test */
    public function cin_accepts_dashed_format_and_rejects_invalid(): void
    {
        $rule = new Cin;

        $this->assertRulePasses($rule, self::VALID_DASHED);
        $this->assertRuleFails($rule, '1234567890');
        $this->assertRuleFails($rule, '12-345-678-9');
    }

    /** @test */
    public function nif_uses_same_dashed_format_as_cin(): void
    {
        $rule = new Nif;

        $this->assertRulePasses($rule, self::VALID_DASHED);
        $this->assertRuleFails($rule, '1234567890123');
    }

    /** @test */
    public function ninu_requires_ten_digits_without_dashes(): void
    {
        $rule = new Ninu;

        $this->assertRulePasses($rule, '1234567890');
        $this->assertRuleFails($rule, self::VALID_DASHED);
        $this->assertRuleFails($rule, '123456789');
    }

    /** @test */
    public function nif_ninu_accepts_dashed_identification_format(): void
    {
        $rule = new NifNinu;

        $this->assertRulePasses($rule, self::VALID_DASHED);
        $this->assertRuleFails($rule, '1234567890');
    }
}
