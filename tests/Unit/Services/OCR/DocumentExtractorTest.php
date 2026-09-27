<?php

namespace Tests\Unit\Services\OCR;

use App\Enums\TypeDemandeEnum;
use App\Services\OCR\DocumentExtractor;
use Tests\TestCase;

class DocumentExtractorTest extends TestCase
{
    private DocumentExtractor $extractor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extractor = new DocumentExtractor;
    }

    /** @test */
    public function it_extracts_identity_fields_from_ocr_text(): void
    {
        $text = <<<TXT
        REPUBLIQUE D'HAITI
        NOM : DUPONT
        PRENOM : Jean Pierre
        NIF : 123-456-789-0
        NINU : 111-222-333-4
        Code pension : PEN-654321
        Email : jean.dupont@example.com
        Telephone : +509 38123456
        TXT;

        $data = $this->extractor->extractFields($text, TypeDemandeEnum::DEMANDE_CREATION_COMPTE);

        $this->assertSame('DUPONT', $data['nom']);
        $this->assertSame('Jean Pierre', $data['prenom']);
        $this->assertSame('1234567890', $data['nif']);
        $this->assertSame('PEN-654321', $data['code_pension']);
        $this->assertSame('jean.dupont@example.com', $data['email']);
        $this->assertSame('+50938123456', $data['telephone']);
    }

    /** @test */
    public function it_builds_json_ready_key_value_pairs_from_labeled_lines(): void
    {
        $text = <<<TXT
        NOM : DUPONT
        Date de naissance : 12/03/1952
        Lieu de naissance : Cap-Haïtien
        TXT;

        $data = $this->extractor->extractKeyValues($text, TypeDemandeEnum::DEMANDE_CREATION_COMPTE, [
            'Sexe' => 'M',
        ]);

        $this->assertSame('DUPONT', $data['nom']);
        $this->assertSame('12/03/1952', $data['date_naissance']);
        $this->assertSame('Cap-Haïtien', $data['lieu_naissance']);
        $this->assertSame('M', $data['sexe']);
    }

    /** @test */
    public function it_extracts_haitian_national_id_card_fields(): void
    {
        $text = <<<TXT
        REPUBLIQUE D'HAITI
        REPUBLIK DAYITI
        CARTE D'IDENTIFICATION NATIONALE
        KAT IDANTIFIKASYON NASYONAL
        Prénom / Non
        PIERRE
        RUBENS
        Nom / Siyati
        MILORME
        Lieu de Naissance / Kote ou fèt
        SUD-EST-JACMEL
        Date d'émission /
        Dat kat la fèt
        11-FEB-2020
        Signature du titulaire /
        de la titulaire / Siyati mèt kat la
        Numéro de carte / Nimewo kat la
        H000LE953
        Sexe / Sèks
        M
        Nationalité / Nasyonalite
        HTI
        Date de Naissance / Dat ou fèt
        31-07-1990
        Date d'expiration /
        Dat kat la fini
        13-02-2030
        Numéro d'identification unique
        Nimewo Idantifikasyon Inik
        1148519577
        CS Scanned with CamScanner
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('MILORME', $data['nom']);
        $this->assertSame('PIERRE RUBENS', $data['prenom']);
        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('SUD-EST-JACMEL', $data['lieu_naissance']);
        $this->assertSame('M', $data['sexe']);
        $this->assertSame('HTI', $data['nationalite']);
        $this->assertSame('11/02/2020', $data['date_emission']);
        $this->assertSame('13/02/2030', $data['date_expiration']);
        $this->assertSame('H000LE953', $data['numero_carte']);
        $this->assertSame('1148519577', $data['numero_identification_unique']);
        $this->assertSame('cin', $data['type_document']);
        $this->assertSame(
            ['type_document', 'numero_carte', 'prenom', 'nom', 'sexe', 'nationalite', 'date_naissance', 'lieu_naissance', 'date_emission', 'date_expiration', 'numero_identification_unique'],
            array_keys($data)
        );
    }

    /** @test */
    public function it_extracts_haitian_passport_fields_including_mrz(): void
    {
        $text = <<<TXT
        Numéro d'identification nationale
        11-48-51-9577
        3
        PASPO
        PASSEPORT
        AYITI / HAITI
        Kalite/Type Peyi ki fè // Pays émetteur Paspò nimewo / N" Passeport
        P
        Siyati/Nom
        HTI
        MILORME
        Non/Prénoms
        PIERRE RUBENS
        Moun ki peyi/Nationalité
        Haïtienne
        Dat li fet/Date de naissance
        31 Jiy/Juil 90
        Seks/Sexe CAN
        M
        895018
        Dat paspò a fèét / Date d'émission
        30 Sep /Sept 21
        Dat paspò a fini / Date d'expiration
        29 Sep /Sept 31
        NIF/N personnel
        0045216364
        Wote / Taille
        170 cm
        Kote li fèt/Lieu de naissance
        Jacmel HTI
        Otorite/Autorité
        DIE Bureau Saint Louis du Nord, HAITI
        Siyali mét paspo a / Signature du titulaire
        P<HTIMILORME<<PIERRERUBENS<<<<<<<<<<<<<<<<<<
        R108571122HT19007312M31092920045216364<<<<14
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('MILORME', $data['nom']);
        $this->assertSame('PIERRE RUBENS', $data['prenom']);
        $this->assertSame('0045216364', $data['nif']);
        $this->assertSame('0045216364', $data['numero_personnel']);
        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('M', $data['sexe']);
        $this->assertSame('895018', $data['can']);
        $this->assertSame('R10857112', $data['numero_passeport']);
        $this->assertSame('170 cm', $data['taille']);
        $this->assertSame('Jacmel HTI', $data['lieu_naissance']);
        $this->assertSame('30/09/2021', $data['date_emission']);
        $this->assertSame('29/09/2031', $data['date_expiration']);
        $this->assertSame('passeport', $data['type_document']);
        $this->assertArrayNotHasKey('ninu', $data);
        $this->assertSame(
            ['type_document', 'numero_passeport', 'prenom', 'nom', 'nationalite', 'nif', 'taille', 'numero_personnel', 'date_naissance', 'lieu_naissance', 'sexe', 'can', 'date_emission', 'date_expiration'],
            array_keys($data)
        );
    }

    /** @test */
    public function it_keeps_a_single_passport_given_name_without_adding_spaces(): void
    {
        $text = <<<TXT
        PASSEPORT
        Siyati/Nom
        MILORME
        Non/Prénoms
        PIERRE
        P<HTIMILORME<<PIERRE<<<<<<<<<<<<<<<<<<<<<<<<<
        R108571122HT19007312M31092920045216364<<<<14
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('PIERRE', $data['prenom']);
        $this->assertSame('MILORME', $data['nom']);
    }

    /** @test */
    public function it_extracts_driving_licence_fields(): void
    {
        $text = <<<TXT
        REPUBLIQUE D'HAITI
        PERMIS DE CONDUIRE
        Dossier
        DL-123456
        NIF
        123-456-789-0
        Nom
        MILORME
        Prénom
        PIERRE RUBENS
        Adresse
        12 Rue Capois
        Port-au-Prince
        Date de naissance
        31/07/1990
        Type
        B
        Sexe
        M
        Groupe sanguin
        O+
        Lieu d'émission
        Port-au-Prince
        Émis le
        15/03/2022
        Expire le
        15/03/2027
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('permis', $data['type_document']);
        $this->assertSame('DL-123456', $data['dossier']);
        $this->assertSame('1234567890', $data['nif']);
        $this->assertSame('MILORME', $data['nom']);
        $this->assertSame('PIERRE RUBENS', $data['prenom']);
        $this->assertSame('12 Rue Capois Port-au-Prince', $data['adresse']);
        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('B', $data['type']);
        $this->assertSame('M', $data['sexe']);
        $this->assertSame('O+', $data['groupe_sanguin']);
        $this->assertSame('Port-au-Prince', $data['lieu_emission']);
        $this->assertSame('15/03/2022', $data['emis_le']);
        $this->assertSame('15/03/2027', $data['expire_le']);
        $this->assertSame(
            ['type_document', 'dossier', 'nif', 'nom', 'prenom', 'adresse', 'date_naissance', 'type', 'sexe', 'groupe_sanguin', 'lieu_emission', 'emis_le', 'expire_le'],
            array_keys($data)
        );
    }

    /** @test */
    public function it_extracts_driving_licence_fields_when_values_are_on_the_same_line(): void
    {
        $text = <<<TXT
        PERMIS DE CONDUIRE
        DRIVING LICENCE
        1. Nom/Name MILORME
        2. Prénoms/First names PIERRE RUBENS
        3. Date de naissance/Date of birth 31.07.1990
        4a. Date of issue 15.03.2022 4b. Date of expiry 15.03.2027
        4c. Lieu d'émission Port-au-Prince
        5. Dossier DL-123456
        8. Adresse 12 Rue Capois
        9. Catégories B
        NIF 123-456-789-0
        Sexe M
        Groupe sanguin O+
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('permis', $data['type_document']);
        $this->assertSame('MILORME', $data['nom']);
        $this->assertSame('PIERRE RUBENS', $data['prenom']);
        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('O+', $data['groupe_sanguin']);
        $this->assertSame('Port-au-Prince', $data['lieu_emission']);
        $this->assertSame('15/03/2027', $data['expire_le']);
        $this->assertSame('15/03/2022', $data['emis_le']);
        $this->assertSame('B', $data['type']);
        $this->assertSame('1234567890', $data['nif']);
    }

    /** @test */
    public function it_extracts_haitian_circulation_licence_grid(): void
    {
        $text = <<<TXT
        REPUBLIQUE D'HAITI
        Service de la Circulation
        PERMIS DE CONDUIRE
        Dossier
        UU-32806-BC
        NIF
        004-521-636-4
        Nom
        MILORME, PIERRE RUBENS
        Adresse
        LAMANDOU1, JACMEL
        Type
        BC
        Date de Naissance
        Sexe
        G. Sang
        31/07/1990
        M
        O+
        Lieu d'émission
        Emis le
        Expire le
        P-AU-P
        29/12/2023
        31/07/2028
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('permis', $data['type_document']);
        $this->assertSame('UU-32806-BC', $data['dossier']);
        $this->assertSame('0045216364', $data['nif']);
        $this->assertSame('MILORME', $data['nom']);
        $this->assertSame('PIERRE RUBENS', $data['prenom']);
        $this->assertSame('LAMANDOU1, JACMEL', $data['adresse']);
        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('BC', $data['type']);
        $this->assertSame('M', $data['sexe']);
        $this->assertSame('O+', $data['groupe_sanguin']);
        $this->assertSame('P-AU-P', $data['lieu_emission']);
        $this->assertSame('29/12/2023', $data['emis_le']);
        $this->assertSame('31/07/2028', $data['expire_le']);
    }

    /** @test */
    public function it_extracts_haitian_birth_certificate_fields(): void
    {
        $text = <<<TXT
        REPUBLIQUE D'HAITI
        ACTE DE NAISSANCE
        Numéro d'acte : 12345
        Nom : MILORME
        Prénom : PIERRE RUBENS
        Sexe : M
        Date de naissance : 31/07/1990
        Lieu de naissance : JACMEL
        Père : JEAN MILORME
        Mère : MARIE LOUIS
        Commune : JACMEL
        Date de l'acte : 05/08/1990
        TXT;

        $data = $this->extractor->extractKeyValues($text, TypeDemandeEnum::DEMANDE_CREATION_COMPTE);

        $this->assertSame('acte_naissance', $data['type_document']);
        $this->assertSame('12345', $data['numero_acte']);
        $this->assertSame('MILORME', $data['nom']);
        $this->assertSame('PIERRE RUBENS', $data['prenom']);
        $this->assertSame('M', $data['sexe']);
        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('JACMEL', $data['lieu_naissance']);
        $this->assertSame('JEAN MILORME', $data['nom_pere']);
        $this->assertSame('MARIE LOUIS', $data['nom_mere']);
        $this->assertSame('JACMEL', $data['commune']);
        $this->assertSame('05/08/1990', $data['date_acte']);
    }

    /** @test */
    public function it_does_not_use_birth_date_as_licence_issue_date(): void
    {
        $text = <<<TXT
        PERMIS DE CONDUIRE
        Nom
        MILORME, PIERRE RUBENS
        Date de Naissance
        31/07/1990
        Emis le
        29/12/2023
        Expire le
        31/07/2028
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('29/12/2023', $data['emis_le']);
        $this->assertSame('31/07/2028', $data['expire_le']);
    }

    /** @test */
    public function it_rebalances_misaligned_haitian_licence_ocr(): void
    {
        $text = <<<TXT
        PERMIS DE CONDUIRE
        Dossier
        NIF
        UU-32806-BC
        Nom
        004-521-636-4
        MILORME, PIERRE RUBENS
        Adresse
        LAMANDOU1, JACMEL
        Type
        BC
        Date de Naissance
        Sexe
        G. Sang
        31/07/1990
        M
        0+
        Lieu d'émission Emis le
        le
        P-AU-P
        Expire le
        29/12/2023
        31/07/2028
        TXT;

        $data = $this->extractor->extractKeyValues($text);

        $this->assertSame('MILORME', $data['nom']);
        $this->assertSame('PIERRE RUBENS', $data['prenom']);
        $this->assertSame('0045216364', $data['nif']);
        $this->assertSame('O+', $data['groupe_sanguin']);
        $this->assertSame('P-AU-P', $data['lieu_emission']);
        $this->assertSame('31/07/1990', $data['date_naissance']);
        $this->assertSame('29/12/2023', $data['emis_le']);
        $this->assertSame('31/07/2028', $data['expire_le']);
        $this->assertSame('UU-32806-BC', $data['dossier']);
    }

    /** @test */
    public function it_omits_fields_that_are_not_in_the_text(): void
    {
        $data = $this->extractor->extractFields(
            'Attestation sans identifiant',
            TypeDemandeEnum::DEMANDE_ATTESTATION
        );

        $this->assertSame([], $data);
    }

    /** @test */
    public function it_extracts_a_bank_account_for_virement_requests(): void
    {
        $text = "NIF 987-654-321-0\nCompte : 001234567890";

        $data = $this->extractor->extractFields($text, TypeDemandeEnum::DEMANDE_VIREMENT_BANCAIRE);

        $this->assertSame('9876543210', $data['nif']);
        $this->assertSame('001234567890', $data['compte_bancaire']);
    }
}
