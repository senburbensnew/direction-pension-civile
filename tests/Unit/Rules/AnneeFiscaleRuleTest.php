<?php

namespace Tests\Unit\Rules;

use App\Rules\AnneeFiscale;
use Tests\Support\ValidatesValidationRule;
use Tests\TestCase;

class AnneeFiscaleRuleTest extends TestCase
{
    use ValidatesValidationRule;

    /** @test */
    public function it_accepts_consecutive_years(): void
    {
        $this->assertRulePasses(new AnneeFiscale, '2024/2025');
    }

    /** @test */
    public function it_rejects_wrong_format(): void
    {
        $this->assertRuleFails(new AnneeFiscale, '2024-2025');
        $this->assertRuleFails(new AnneeFiscale, '2024');
    }

    /** @test */
    public function it_rejects_non_consecutive_years(): void
    {
        $message = $this->assertRuleFails(new AnneeFiscale, '2024/2026');

        $this->assertStringContainsString('consécutives', $message);
    }
}
