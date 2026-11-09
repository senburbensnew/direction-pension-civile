<?php

namespace Tests\Unit\Rules;

use Tests\Support\AppClassDiscovery;
use Tests\TestCase;

class RuleDedicatedTestCoverageTest extends TestCase
{
    /** @var array<class-string, non-empty-string> */
    private const RULE_TO_TEST_FILE = [
        \App\Rules\Cin::class => 'IdentificationFormatRulesTest.php',
        \App\Rules\Nif::class => 'IdentificationFormatRulesTest.php',
        \App\Rules\NifNinu::class => 'IdentificationFormatRulesTest.php',
        \App\Rules\Ninu::class => 'IdentificationFormatRulesTest.php',
        \App\Rules\NumeroIdentification::class => 'PlaceholderRulesTest.php',
        \App\Rules\NumeroCompte::class => 'PlaceholderRulesTest.php',
        \App\Rules\Base64Image::class => 'Base64ImageRuleTest.php',
        \App\Rules\AnneeFiscale::class => 'AnneeFiscaleRuleTest.php',
        \App\Rules\CodePension::class => 'CodePensionRuleTest.php',
        \App\Rules\Telephone::class => 'TelephoneRuleTest.php',
        \App\Rules\PensionnaireReferenceExists::class => 'PensionnaireReferenceExistsTest.php',
    ];

    /** @test */
    public function every_app_validation_rule_is_covered_by_a_unit_test_file(): void
    {
        $dir = base_path('tests/Unit/Rules');

        foreach (AppClassDiscovery::validationRuleClasses() as $ruleClass) {
            $file = self::RULE_TO_TEST_FILE[$ruleClass] ?? null;

            $this->assertNotNull(
                $file,
                "Ajoute une entrée RULE_TO_TEST_FILE pour {$ruleClass}"
            );

            $this->assertFileExists(
                $dir.DIRECTORY_SEPARATOR.$file,
                "Le fichier {$file} doit couvrir {$ruleClass}"
            );
        }
    }
}
