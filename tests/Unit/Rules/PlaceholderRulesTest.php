<?php

namespace Tests\Unit\Rules;

use App\Rules\NumeroCompte;
use App\Rules\NumeroIdentification;
use Tests\Support\ValidatesValidationRule;
use Tests\TestCase;

class PlaceholderRulesTest extends TestCase
{
    use ValidatesValidationRule;

    /** @test */
    public function numero_compte_is_currently_a_no_op(): void
    {
        $this->assertRulePasses(new NumeroCompte, null);
        $this->assertRulePasses(new NumeroCompte, 'anything');
    }

    /** @test */
    public function numero_identification_is_currently_a_no_op(): void
    {
        $this->assertRulePasses(new NumeroIdentification, null);
        $this->assertRulePasses(new NumeroIdentification, 'anything');
    }
}
