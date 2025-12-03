<?php

namespace App\View\Components;

use Illuminate\View\Component;

class InstitutionsCarousel extends Component
{
    public array $institutions;
    public int $speed; // seconds for CSS animation
    public int $scrollDelay; // milliseconds for JS approach
    public int $scrollSpeed; // milliseconds for smooth scroll
    public string $carouselId;

    public function __construct(
        int $speed = 60, // CSS animation duration in seconds
        int $scrollDelay = 3000, // JS scroll delay in ms
        int $scrollSpeed = 800, // JS scroll speed in ms
        array $customInstitutions = []
    ) {
        $this->speed = $speed;
        $this->scrollDelay = $scrollDelay;
        $this->scrollSpeed = $scrollSpeed;
        $this->carouselId = 'institutions-carousel-' . uniqid();

        $this->institutions = !empty($customInstitutions)
            ? $customInstitutions
            : $this->getDefaultInstitutions();
    }

    private function getDefaultInstitutions(): array
    {
        return [
            ['name' => 'Ministère de l\'Economie et des Finances', 'logo' => 'images/logo_MEF.png'],
            ['name' => 'Banque Interamericaine de Développement', 'logo' => 'images/logo_BID.svg'],
            ['name' => 'Office d\'Assurance Véhicules Contre Tiers', 'logo' => 'images/logo_OAVCT.png'],
            ['name' => 'Unité de Lutte Contre la Corruption', 'logo' => 'images/logo_ulcc.jpg'],
            ['name' => 'Office d\'Assurance Accidents du Travail, Maladie et Maternité', 'logo' => 'images/logo_ofatma.jpg'],
            ['name' => 'Office des Postes d\'Haiti', 'logo' => 'images/logo_oph.png'],
            ['name' => 'Ministère de l\'Intérieur et des Collectivités Territoriales', 'logo' => 'images/setting-logo-1-M13oPLiYoM.png'],
            ['name' => 'Ministère de la Culture et de la Communication', 'logo' => 'images/logo_mcc.jpg'],
            ['name' => 'Ministère du Commerce Et de l\'Industrie', 'logo' => 'images/logo_mci.png'],
            ['name' => 'Ministère des Travaux Publics, Transports et Communications', 'logo' => 'images/logo_mtptc.jpg'],
            ['name' => 'Ministère des Haïtiens Vivant à l\'Etranger', 'logo' => 'images/setting-logo-1-M13oPLiYoM.png'],
            ['name' => 'Ministère de la Santé Publique et de la Population', 'logo' => 'images/logo_mspp.png'],
            ['name' => 'Ministère de l\'Éducation Nationale et de la Formation Professionnelle', 'logo' => 'images/logo_menfp.jpg'],
        ];
    }

    public function render()
    {
        return view('components.institutions-carousel');
    }
}
