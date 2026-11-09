<?php

namespace Tests\Unit\Services\Pension;

use App\Enums\AccountStatusEnum;
use App\Models\AffiliationPension;
use App\Models\Beneficiaire;
use App\Services\Pension\ActivePensionContext;
use App\Services\Pension\BeneficiaireProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivePensionContextTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guest_has_no_accessible_beneficiaires(): void
    {
        $context = ActivePensionContext::for(null);

        $this->assertTrue($context->accessibleBeneficiaires()->isEmpty());
        $this->assertNull($context->resolveBeneficiaire());
    }

    /** @test */
    public function pensionne_resolves_primary_code_and_single_beneficiaire(): void
    {
        $user = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-50001');
        $context = ActivePensionContext::for($user);

        $this->assertSame(1, $context->accessibleBeneficiaires()->count());
        $this->assertSame('8-50001', $context->displayPensionCode());
        $this->assertFalse($context->needsContextSwitcher());
    }

    /** @test */
    public function session_selects_beneficiaire_and_affiliation(): void
    {
        $user = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-50002');
        $beneficiaire = $user->beneficiaireLinks()->first()->beneficiaire;

        app(BeneficiaireProvisioningService::class)->syncAffiliation($beneficiaire, '8-50003', false);

        $context = ActivePensionContext::for($user);
        $second = $beneficiaire->affiliations()->where('code_pension', '8-50003')->first();

        $context->setContext($beneficiaire->id, $second->id);

        $this->assertTrue($context->needsContextSwitcher());
        $this->assertSame('8-50003', $context->displayPensionCode());
    }

    /** @test */
    public function ensure_defaults_sets_session_when_missing(): void
    {
        $user = $this->createPensionneUser(['account_status' => AccountStatusEnum::ACTIF], '8-50004');
        $context = ActivePensionContext::for($user);

        session()->forget(ActivePensionContext::SESSION_BENEFICIAIRE);
        $context->ensureDefaults();

        $this->assertNotNull(session(ActivePensionContext::SESSION_BENEFICIAIRE));
    }
}
