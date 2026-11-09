<?php

namespace Tests\Unit\Enums;

use App\Enums\AccountStatusEnum;
use Tests\TestCase;

class AccountStatusEnumTest extends TestCase
{
    /** @test */
    public function default_status_is_actif(): void
    {
        $this->assertSame(AccountStatusEnum::ACTIF, AccountStatusEnum::default());
    }

    /** @test */
    public function allows_login_only_for_actif_and_provisoire(): void
    {
        $canLogin = [AccountStatusEnum::ACTIF, AccountStatusEnum::PROVISOIRE];
        $cannotLogin = [AccountStatusEnum::BLOQUE, AccountStatusEnum::DESACTIVE];

        foreach ($canLogin as $status) {
            $this->assertTrue($status->allowsLogin(), $status->value);
        }
        foreach ($cannotLogin as $status) {
            $this->assertFalse($status->allowsLogin(), $status->value);
        }
    }

    /** @test */
    public function status_predicates_match_cases(): void
    {
        $this->assertTrue(AccountStatusEnum::PROVISOIRE->isProvisionnel());
        $this->assertFalse(AccountStatusEnum::ACTIF->isProvisionnel());
        $this->assertTrue(AccountStatusEnum::ACTIF->isActif());
        $this->assertTrue(AccountStatusEnum::DESACTIVE->isDisabledByAdmin());
    }

    /** @test */
    public function try_from_stored_normalizes_legacy_and_empty_values(): void
    {
        $this->assertSame(AccountStatusEnum::ACTIF, AccountStatusEnum::tryFromStored(null));
        $this->assertSame(AccountStatusEnum::ACTIF, AccountStatusEnum::tryFromStored(''));
        $this->assertSame(AccountStatusEnum::PROVISOIRE, AccountStatusEnum::tryFromStored('en_attente_validation'));
        $this->assertSame(AccountStatusEnum::BLOQUE, AccountStatusEnum::tryFromStored('bloque'));
        $this->assertSame(AccountStatusEnum::BLOQUE, AccountStatusEnum::tryFromStored(AccountStatusEnum::BLOQUE));
        $this->assertNull(AccountStatusEnum::tryFromStored(42));
        $this->assertNull(AccountStatusEnum::tryFromStored('invalid_status'));
    }

    /** @test */
    public function all_cases_have_french_labels(): void
    {
        foreach (AccountStatusEnum::cases() as $case) {
            $this->assertNotEmpty($case->label(), "Empty label for {$case->value}");
        }
    }
}
