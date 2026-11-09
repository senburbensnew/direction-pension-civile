<?php

namespace Tests\Unit\Rules;

use App\Models\PensionnaireReference;
use App\Rules\PensionnaireReferenceExists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\ValidatesValidationRule;
use Tests\TestCase;

class PensionnaireReferenceExistsTest extends TestCase
{
    use RefreshDatabase;
    use ValidatesValidationRule;

    /** @test */
    public function blank_code_skips_validation(): void
    {
        $this->assertRulePasses(new PensionnaireReferenceExists, null);
        $this->assertRulePasses(new PensionnaireReferenceExists, '');
    }

    /** @test */
    public function unknown_code_fails(): void
    {
        $message = $this->assertRuleFails(new PensionnaireReferenceExists, '8-99999');

        $this->assertStringContainsString("n'existe pas", $message);
    }

    /** @test */
    public function known_code_without_name_fields_passes(): void
    {
        $this->seedReference('8-34321', 'PIERRE', 'JEAN');

        $this->assertRulePasses(new PensionnaireReferenceExists, '8-34321');
    }

    /** @test */
    public function matching_nom_and_prenom_passes(): void
    {
        $this->seedReference('8-34321', 'PIERRE', 'JEAN');

        $this->assertRulePasses(new PensionnaireReferenceExists, '8-34321', [
            'nom' => 'Pierre',
            'prenom' => 'Jean',
        ]);
    }

    /** @test */
    public function mismatched_identity_fails(): void
    {
        $this->seedReference('8-34321', 'MARTIN', 'PAUL');

        $message = $this->assertRuleFails(new PensionnaireReferenceExists, '8-34321', [
            'nom' => 'Pierre',
            'prenom' => 'Jean',
        ]);

        $this->assertStringContainsString('ne correspond pas', $message);
    }

    /** @test */
    public function mandataire_skips_identity_match(): void
    {
        $this->seedReference('8-34321', 'MARTIN', 'PAUL');

        $this->assertRulePasses(new PensionnaireReferenceExists, '8-34321', [
            'is_mandataire' => true,
            'nom' => 'Pierre',
            'prenom' => 'Jean',
        ]);
    }

    /** @test */
    public function row_matches_identity_uses_token_overlap(): void
    {
        $rule = new PensionnaireReferenceExists;
        $row = new PensionnaireReference([
            'NOM' => 'DUPONT',
            'PRENOM' => 'JEAN PIERRE',
        ]);

        $this->assertTrue($rule->rowMatchesIdentity($row, 'Jean', 'Dupont'));
        $this->assertFalse($rule->rowMatchesIdentity($row, 'Marie', 'Curie'));
    }

    private function seedReference(string $code, string $nom, string $prenom): void
    {
        PensionnaireReference::query()->updateOrCreate(
            ['PENSIONNAIRE_ID' => strtoupper(trim($code))],
            ['NOM' => $nom, 'PRENOM' => $prenom]
        );
    }
}
