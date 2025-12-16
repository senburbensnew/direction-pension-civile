<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Presentation extends Component
{
    public string $role;
    public string $nom;
    public string $sexe;
    public string $lienProfil;
    public string $lienDiscours;
    public string $mobileImage;
    public string $desktopImage;
    public bool $showProfileLink;
    public bool $showSpeechLink;

    /**
     * Create a new component instance.
     */
    public function __construct(
        string $role,
        string $nom,
        string $sexe,
        string $lienProfil = '',
        string $lienDiscours = '',
        string $mobileImage = '',
        string $desktopImage = '',
        bool $showProfileLink = false,
        bool $showSpeechLink = false
    ) {
        $this->role = $role;
        $this->nom = $nom;
        $this->sexe = $sexe;
        $this->lienProfil = $lienProfil;
        $this->lienDiscours = $lienDiscours;
        $this->mobileImage = $mobileImage;
        $this->desktopImage = $desktopImage;
        $this->showProfileLink = $showProfileLink;  // fixed
        $this->showSpeechLink = $showSpeechLink;    // fixed
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.presentation');
    }
}
