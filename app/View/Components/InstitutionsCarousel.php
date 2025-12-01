<?php

namespace App\View\Components;

use Illuminate\View\Component;

class InstitutionsCarousel extends Component
{
    public array $institutions;

    public function __construct()
    {
        // You can put this in a config file or DB later
        $this->institutions = [
            ['name' => 'Ministère de l\'Economie et des Finances', 'logo' => 'images/logo_MEF.png'],
            ['name' => 'Banque Interaméricaine de Développement', 'logo' => 'images/logo_BID.svg'],

            ['name' => 'Ministère de l\'Economie et des Finances', 'logo' => 'images/logo_MEF.png'],
            ['name' => 'Banque Interaméricaine de Développement', 'logo' => 'images/logo_BID.svg'],

            ['name' => 'Ministère de l\'Economie et des Finances', 'logo' => 'images/logo_MEF.png'],
            ['name' => 'Banque Interaméricaine de Développement', 'logo' => 'images/logo_BID.svg'],
        ];
    }

    public function render()
    {
        return view('components.institutions-carousel');
    }
}
