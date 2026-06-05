<?php

namespace App\View\Components;

use App\Models\Partenaire;
use Illuminate\View\Component;

class InstitutionsCarousel extends Component
{
    public array $institutions;
    public int $speed;

    public function __construct(int $speed = 60, array $customInstitutions = [])
    {
        $this->speed = $speed;

        if (!empty($customInstitutions)) {
            $this->institutions = $customInstitutions;
            return;
        }

        $db = Partenaire::active()->ordered()->get();

        if ($db->isNotEmpty()) {
            $this->institutions = $db->map(fn ($p) => [
                'name' => $p->name,
                'logo' => $p->logoUrl(),
                'url'  => $p->website_url,
            ])->toArray();
        } else {
            $this->institutions = $this->getDefaultInstitutions();
        }
    }

    private function getDefaultInstitutions(): array
    {
        return [
            ['name' => 'Ministère de l\'Economie et des Finances',                              'logo' => asset('images/logo_MEF.png'),   'url' => null],
            ['name' => 'Banque Interaméricaine de Développement',                                'logo' => asset('images/logo_BID.svg'),   'url' => null],
            ['name' => 'Office d\'Assurance Véhicules Contre Tiers',                             'logo' => asset('images/logo_OAVCT.png'), 'url' => null],
            ['name' => 'Unité de Lutte Contre la Corruption',                                    'logo' => asset('images/logo_ulcc.jpg'),  'url' => null],
            ['name' => 'Office d\'Assurance Accidents du Travail, Maladie et Maternité',         'logo' => asset('images/logo_ofatma.jpg'),'url' => null],
            ['name' => 'Office des Postes d\'Haïti',                                             'logo' => asset('images/logo_oph.png'),   'url' => null],
            ['name' => 'Ministère de l\'Intérieur et des Collectivités Territoriales',           'logo' => null,                           'url' => null],
            ['name' => 'Ministère de la Culture et de la Communication',                         'logo' => asset('images/logo_mcc.jpg'),   'url' => null],
            ['name' => 'Ministère du Commerce et de l\'Industrie',                               'logo' => asset('images/logo_mci.png'),   'url' => null],
            ['name' => 'Ministère des Travaux Publics, Transports et Communications',            'logo' => asset('images/logo_mtptc.jpg'), 'url' => null],
            ['name' => 'Ministère des Haïtiens Vivant à l\'Étranger',                           'logo' => null,                           'url' => null],
            ['name' => 'Ministère de la Santé Publique et de la Population',                     'logo' => asset('images/logo_mspp.png'),  'url' => null],
            ['name' => 'Ministère de l\'Éducation Nationale et de la Formation Professionnelle', 'logo' => asset('images/logo_menfp.jpg'), 'url' => null],
        ];
    }

    public function render()
    {
        return view('components.institutions-carousel');
    }
}
