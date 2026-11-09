<?php

namespace Tests\Unit\Helpers;

use App\Helpers\RegexExpressions;
use Tests\TestCase;

class RegexExpressionsTest extends TestCase
{
    /** @test */
    public function pension_code_pattern_matches_expected_samples(): void
    {
        $pattern = RegexExpressions::pensionCode();

        $this->assertMatchesRegularExpression($pattern, '8-34321');
        $this->assertMatchesRegularExpression($pattern, 'ABC-12345');
        $this->assertDoesNotMatchRegularExpression($pattern, 'x');
    }

    /** @test */
    public function ninu_pattern_matches_dashed_ten_digit_style(): void
    {
        $pattern = RegexExpressions::ninu();

        $this->assertMatchesRegularExpression($pattern, '123-456-789-0');
        $this->assertDoesNotMatchRegularExpression($pattern, '1234567890');
    }

    /** @test */
    public function phone_number_pattern_accepts_haiti_formats(): void
    {
        $pattern = RegexExpressions::phoneNumber();

        $this->assertMatchesRegularExpression($pattern, '+5093456789');
        $this->assertMatchesRegularExpression($pattern, '3456789');
        $this->assertMatchesRegularExpression($pattern, '2234567');
        $this->assertMatchesRegularExpression($pattern, '8123456');
    }

    /** @test */
    public function fiscal_year_pattern_requires_two_years(): void
    {
        $pattern = RegexExpressions::fiscalYear();

        $this->assertMatchesRegularExpression($pattern, '2024/2025');
        $this->assertDoesNotMatchRegularExpression($pattern, '1999/2000');
    }
}
