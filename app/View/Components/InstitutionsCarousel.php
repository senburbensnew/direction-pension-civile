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

        $this->institutions = Partenaire::active()->ordered()->get()
            ->map(fn ($p) => [
                'name' => $p->name,
                'logo' => $p->logoUrl(),
                'url'  => $p->website_url,
            ])->toArray();
    }

    public function render()
    {
        return view('components.institutions-carousel');
    }
}
