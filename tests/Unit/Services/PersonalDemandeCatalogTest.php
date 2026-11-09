<?php

namespace Tests\Unit\Services;

use App\Enums\TypeDemandeEnum;
use App\Services\PersonalDemandeCatalog;
use Tests\TestCase;

class PersonalDemandeCatalogTest extends TestCase
{
    private PersonalDemandeCatalog $catalog;

    protected function setUp(): void
    {
        parent::setUp();
        $this->catalog = new PersonalDemandeCatalog;
    }

    /** @test */
    public function sections_expose_pensionne_and_fonctionnaire_entries(): void
    {
        $sections = $this->catalog->sections();

        $this->assertArrayHasKey('pensionne', $sections);
        $this->assertArrayHasKey('fonctionnaire', $sections);
        $this->assertSame(
            TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE,
            $sections['pensionne'][0]['enum']
        );
    }

    /** @test */
    public function find_returns_entry_by_request_type_key(): void
    {
        $entry = $this->catalog->find('certificateRequest');

        $this->assertNotNull($entry);
        $this->assertSame(TypeDemandeEnum::DEMANDE_ATTESTATION, $entry['enum']);
    }

    /** @test */
    public function corbeille_type_map_includes_rencontre_and_account_creation(): void
    {
        $map = $this->catalog->corbeilleTypeMap();

        $this->assertSame(TypeDemandeEnum::DEMANDE_RENCONTRE, $map['rencontreRequest']['enum']);
        $this->assertSame(TypeDemandeEnum::DEMANDE_CREATION_COMPTE, $map['accountCreationRequest']['enum']);
    }
}
